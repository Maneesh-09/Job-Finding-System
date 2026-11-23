"""Integration regression checks using an isolated MariaDB and PHP server."""
import argparse
import base64
from datetime import date, timedelta
import http.cookiejar
import os
from pathlib import Path
import re
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
FLAGS = subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0


def port():
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        return sock.getsockname()[1]


def wait_port(number, process):
    for _ in range(100):
        if process.poll() is not None:
            raise RuntimeError('Test server exited before starting')
        try:
            with socket.create_connection(('127.0.0.1', number), timeout=.2):
                return
        except OSError:
            time.sleep(.1)
    raise RuntimeError('Test server did not start')


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args):
        return None


class Browser:
    def __init__(self, base):
        self.base = base
        self.cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(
            NoRedirect(), urllib.request.HTTPCookieProcessor(self.cookies))

    def request(self, path, fields=None, files=None):
        headers = {}
        data = None
        if files is not None:
            boundary = 'jfc-integration-boundary'
            parts = []
            for key, value in fields.items():
                parts.append((f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n').encode())
            for key, (name, mime, body) in files.items():
                parts.append((f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"; filename="{name}"\r\nContent-Type: {mime}\r\n\r\n').encode() + body + b'\r\n')
            data = b''.join(parts) + f'--{boundary}--\r\n'.encode()
            headers['Content-Type'] = f'multipart/form-data; boundary={boundary}'
        elif fields is not None:
            data = urllib.parse.urlencode(fields, doseq=True).encode()
        req = urllib.request.Request(self.base + path, data=data, headers=headers)
        try:
            response = self.opener.open(req, timeout=15)
        except urllib.error.HTTPError as error:
            response = error
        body = response.read().decode('utf-8', errors='replace')
        assert not re.search(r'(Fatal error|Warning|Deprecated|Notice|Parse error):', body), (path, body[-1500:])
        assert response.code < 500, (path, response.code, body)
        return response.code, response.headers, body

    def login(self, role, username, password):
        status, headers, _ = self.request('/' + role + '.php', {'username': username, 'password': password})
        assert status == 302 and headers['Location'] == role + 'homepage.php', (role, status, headers)


def run(mysql_bin):
    suffix = '.exe' if os.name == 'nt' else ''
    mysql = str(mysql_bin / ('mysql' + suffix))
    processes = []
    new_uploads = set()
    temp_parent = Path(tempfile.gettempdir()).resolve()
    with tempfile.TemporaryDirectory(prefix='jfc-regression-') as directory:
        temp = Path(directory).resolve()
        assert temp.parent == temp_parent and temp.name.startswith('jfc-regression-')
        db_port, web_port = port(), port()
        env = dict(os.environ, DB_HOST='127.0.0.1', DB_PORT=str(db_port), DB_USER='root', DB_PASSWORD='', DB_NAME='jfc_regression')
        db_log = (temp / 'mysql.log').open('wb')
        php_log = (temp / 'php.log').open('wb')

        def sql(query, database='jfc_regression'):
            result = subprocess.run([mysql, '--no-defaults', '--host=127.0.0.1', f'--port={db_port}', '--user=root', '--batch', '--skip-column-names'] + ([database] if database else []), input=query.encode(), capture_output=True, creationflags=FLAGS)
            assert result.returncode == 0, result.stderr.decode(errors='replace')
            return result.stdout.decode().strip()

        def track_upload(folder, name):
            if not name:
                return
            parent = (ROOT / folder).resolve()
            path = (parent / name).resolve()
            assert path.parent == parent and re.fullmatch(r'[a-f0-9]{32}\.(pdf|png|jpe?g)', name)
            new_uploads.add(path)

        def track_application_uploads():
            for row in sql('SELECT cv, photo FROM jobapplication').splitlines():
                cv, photo = row.split('\t')
                track_upload('uploads', cv)
                track_upload('images', photo)

        try:
            subprocess.run([str(mysql_bin / ('mysql_install_db' + suffix)), '--datadir=' + str(temp / 'data'), '--port=' + str(db_port)], check=True, capture_output=True, creationflags=FLAGS)
            db = subprocess.Popen([str(mysql_bin / ('mysqld' + suffix)), '--no-defaults', '--basedir=' + str(mysql_bin.parent), '--datadir=' + str(temp / 'data'), '--bind-address=127.0.0.1', '--port=' + str(db_port), '--console'], stdout=db_log, stderr=subprocess.STDOUT, creationflags=FLAGS)
            processes.append(db)
            wait_port(db_port, db)
            sql('CREATE DATABASE jfc_regression CHARACTER SET utf8mb4', database=None)
            sql((ROOT / 'allsql.sql').read_text(encoding='utf-8'))
            for _ in range(2):
                result = subprocess.run(['php', str(ROOT / 'migrate.php')], env=env, capture_output=True, creationflags=FLAGS)
                assert result.returncode == 0, result.stderr.decode()
            php = subprocess.Popen(['php', '-d', 'display_errors=1', '-d', 'error_reporting=32767', '-S', f'127.0.0.1:{web_port}', '-t', str(ROOT)], env=env, stdout=php_log, stderr=subprocess.STDOUT, creationflags=FLAGS)
            processes.append(php)
            wait_port(web_port, php)
            base = f'http://127.0.0.1:{web_port}'
            anon, user, admin, company, outsider = [Browser(base) for _ in range(5)]
            for path in ['index.php', 'jobs.php', 'user.php', 'company.php', 'admin.php', 'userregister.php', 'companyregister.php']:
                assert anon.request('/' + path)[0] == 200, path
            assert anon.request('/register.php')[1]['Location'] == 'userregister.php'
            assert anon.request('/firstadminsetup.php')[0] == 200
            anon.request('/firstadminsetup.php', {'register': '1', 'username': 'testadmin', 'password': ' admin pass '})
            assert sql("SELECT COUNT(*) FROM admin WHERE username='testadmin'") == '1'
            assert anon.request('/firstadminsetup.php')[0] == 302
            admin.login('admin', 'testadmin', ' admin pass ')
            hashed = sql("SELECT password FROM admin WHERE username='testadmin'")
            assert anon.request('/admin.php', {'username': 'testadmin', 'password': hashed})[0] == 200
            assert anon.request('/admin.php', {'username': "' OR 1=1 --", 'password': 'bad'})[0] == 200
            assert anon.request('/user.php', {'username[]': 'bad'})[0] == 200
            # Legacy administrative passwords are upgraded after a successful login.
            sql("INSERT INTO admin VALUES ('legacy', 'legacy-pass'), ('legacy-md5', MD5('legacy-pass'))")
            for username in ['legacy', 'legacy-md5']:
                anon.login('admin', username, 'legacy-pass')
                assert sql(f"SELECT password FROM admin WHERE username='{username}'").startswith('$2y$')
                anon.request('/logout.php')
            company_fields = dict(register='1', cname='A Test Company With A Long Display Name Incorporated', username='testcompany', password='company-pass', email='company@example.com', address='Kathmandu', pan='TEST-001', category='IT', est_year='2020', employees='10')
            company.request('/companyregister.php', company_fields)
            company_fields.update(username='othercompany', pan='TEST-002')
            outsider.request('/companyregister.php', company_fields)
            assert sql('SELECT COUNT(*) FROM company') == '2'
            company.login('company', 'testcompany', 'company-pass')
            outsider.login('company', 'othercompany', 'company-pass')
            registration = dict(register='1', fname='Test', lname='Seeker', username='skilluser', password=' user pass ', email='user@example.com', qualification='Bachelor', gender='Other', custom_skills=" Java, PHP, C++, Chef's knife, PHP, php, Excel, Graphic Design, Communication, Accounting, Customer Service, Project Management ")
            user.request('/userregister.php', registration)
            skills = sql("SELECT skills FROM user WHERE username='skilluser'")
            assert skills.count('PHP') == 1 and len(skills) > 100 and "Chef's knife" in skills, skills
            user.login('user', 'skilluser', ' user pass ')
            assert user.request('/adminhomepage.php')[1]['Location'] == 'admin.php'
            assert user.request('/companyhomepage.php')[1]['Location'] == 'company.php'
            assert company.request('/userprofile.php')[1]['Location'] == 'user.php'
            assert admin.request('/userprofile.php')[1]['Location'] == 'user.php'
            titles = [('PHP Java Developer', 'PHP Java C++', 'IT & Software', False), ('Java Developer', 'Java', 'IT & Software', False), ('JavaScript Engineer', 'JavaScript', 'IT & Software', False), ('C++ Engineer', 'C++', 'IT & Software', False), ('C# Developer', 'C#', 'IT & Software', False), ('Expired PHP Role', 'PHP', 'IT & Software', True), ('Cooking Trainer', "Chef's knife", 'Hospitality & Tourism', False)]
            for title, description, category, expired in titles:
                quote = lambda value: "'" + value.replace("'", "''") + "'"
                expiry = 'DATE_SUB(CURDATE(), INTERVAL 1 DAY)' if expired else 'DATE_ADD(CURDATE(), INTERVAL 7 DAY)'
                sql('INSERT INTO jobs (title, description, location, qualification, salary, image, username, openeddate, expirydate, category, company_id) VALUES (' + ','.join(map(quote, [title, description, 'Kathmandu', 'Bachelor', 'Negotiable', '', 'testcompany'])) + ',NOW(),' + expiry + ',' + quote(category) + ',1)')
            body = user.request('/userviewjobs.php?related=1')[2]
            assert 'PHP Java Developer' in body and 'C++ Engineer' in body and 'Cooking Trainer' in body
            assert 'JavaScript Engineer' not in body and 'C# Developer' not in body and 'Expired PHP Role' not in body
            assert body.index('PHP Java Developer') < body.index('Java Developer')
            assert 'Cooking Trainer' not in user.request('/userviewjobs.php?related=1&category=IT%20%26%20Software')[2]
            assert 'JavaScript Engineer' in user.request('/userviewjobs.php')[2]
            assert 'Expired PHP Role' not in user.request('/userhomepage.php')[2]
            profile = user.request('/userprofile.php')[2]
            assert 'Your Skills' in profile and 'Chef&#039;s knife' in profile and 'custom_skills' in profile
            assert user.request('/applyjob.php')[0] == 404
            assert user.request('/applyjob.php?job_id=6')[0] == 404
            assert user.request('/applyjob.php?job_id=1')[0] == 200
            application = dict(apply='1', job_id='1', fullname='Test Seeker', email='user@example.com', phone='+977 9812345678', address='Kathmandu', skills='PHP, Java', experiences='Project work')
            png = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aSfkAAAAASUVORK5CYII=')
            files = {'cv': ('test.pdf', 'application/pdf', b'%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF'), 'photo': ('test.png', 'image/png', png)}
            bad = dict(files, cv=('test.php', 'text/plain', b'<?php echo 1;'))
            assert user.request('/applyjob.php?job_id=1', application, bad)[0] == 200
            assert sql('SELECT COUNT(*) FROM jobapplication') == '0'
            assert user.request('/applyjob.php?job_id=1', application, files)[0] == 302
            track_application_uploads()
            assert sql('SELECT CONCAT(address, "|", phone, "|", skills) FROM jobapplication') == 'Kathmandu|+977 9812345678|PHP, Java'
            assert user.request('/applyjob.php?job_id=1', application, files)[0] == 200
            assert sql('SELECT COUNT(*) FROM jobapplication') == '1'
            assert outsider.request('/companyacceptrequest.php', dict(accept='1', application_id='1', job_id='1'))[0] == 404
            assert company.request('/companyacceptrequest.php', dict(accept='1', application_id='1', job_id='1'))[0] == 302
            assert sql('SELECT COUNT(*) FROM accepted_application') == '1'
            assert sql('SELECT COUNT(*) FROM jobapplication') == '0'
            assert company.request('/companyacceptrequest.php', dict(accept='1', application_id='1', job_id='1'))[0] == 404
            application['job_id'] = '2'
            assert user.request('/applyjob.php?job_id=2', application, files)[0] == 302
            track_application_uploads()
            assert company.request('/companydeclineuser.php', dict(decline='1', application_id='2', job_id='2'))[0] == 302
            assert sql('SELECT COUNT(*) FROM declined_application') == '1'
            # Custom skill editing, username changes and escaped output.
            update = dict(update='1', fname='Test', lname='Seeker', username='renameduser', password='', email='user@example.com', qualification='Bachelor', gender='Other', custom_skills="PHP, Python, Chef's knife, <script>alert(1)</script>")
            user.request('/userprofile.php', update)
            assert sql("SELECT COUNT(*) FROM accepted_application WHERE username='renameduser'") == '1'
            assert sql("SELECT COUNT(*) FROM declined_application WHERE username='renameduser'") == '1'
            profile = user.request('/userprofile.php')[2]
            assert '<script>alert(1)</script>' not in profile and '&lt;script&gt;alert(1)&lt;/script&gt;' in profile
            assert 'PHP Java Developer' in user.request('/userviewjobs.php?related=1')[2]
            assert 'C++ Engineer' not in user.request('/userviewjobs.php?related=1')[2]
            assert user.request('/userupdateprofile.php?uid=999')[0] == 200
            # Add, remove, and edit custom skills via the separate profile editor.
            editor = dict(update, username='renameduser', custom_skills='PHP, Excel')
            user.request('/userupdateprofile.php?uid=1', editor)
            assert sql("SELECT skills FROM user WHERE uid=1") == 'PHP, Excel'
            editor.update(custom_skills='Python, PHP, Excel')
            admin.request('/adminupdateuser.php?uid=1', editor)
            assert sql("SELECT skills FROM user WHERE uid=1") == 'Python, PHP, Excel'
            invalid = dict(editor, username='x' * 21, password='x' * 73)
            assert user.request('/userprofile.php', invalid)[0] == 200
            assert sql('SELECT username FROM user WHERE uid=1') == 'renameduser'
            # Employer job creation, upload validation, editing, and company settings.
            job_fields = dict(addjob='1', title='Senior PHP Developer and Platform Engineer', description='Build PHP applications', location='Kathmandu', qualification='Bachelor', salary='Negotiable', category='IT & Software', expirydate=str(date.today() + timedelta(days=30)))
            invalid_job = dict(job_fields, expirydate='2000-01-01')
            company.request('/companyaddjobs.php', invalid_job, {'file': ('job.png', 'image/png', png)})
            assert sql('SELECT COUNT(*) FROM jobs') == '7'
            company.request('/companyaddjobs.php', job_fields, {'file': ('job.php', 'text/plain', b'<?php echo 1;')})
            assert sql('SELECT COUNT(*) FROM jobs') == '7'
            company.request('/companyaddjobs.php', job_fields, {'file': ('job.png', 'image/png', png)})
            assert sql('SELECT COUNT(*) FROM jobs') == '8'
            track_upload('images', sql('SELECT image FROM jobs WHERE id=8'))
            job_fields['title'] = 'Another PHP Developer'
            company.request('/addjob.php', job_fields, {'file': ('job.png', 'image/png', png)})
            assert sql('SELECT COUNT(*) FROM jobs') == '9'
            assert sql('SELECT username FROM jobs WHERE id=9') == 'testcompany'
            track_upload('images', sql('SELECT image FROM jobs WHERE id=9'))
            edit_job = dict(job_fields, updatejob='1', title='Updated PHP Role')
            admin.request('/admineditjobs.php?job_id=8', edit_job)
            assert sql('SELECT title FROM jobs WHERE id=8') == 'Updated PHP Role'
            company_profile = dict(update='1', cname='Updated Company Name', password='', email='company@example.com', address='Lalitpur', category='IT', est_year='2021', employees='20')
            company.request('/companyprofile.php', company_profile, {'pan_image': ('pan.png', 'image/png', png), 'company_logo': ('logo.png', 'image/png', png)})
            assert sql('SELECT company_name FROM company WHERE cid=1') == 'Updated Company Name'
            for name in sql('SELECT pan_image, company_logo FROM company WHERE cid=1').split('\t'):
                track_upload('uploads', name)
            for path in ['adminhomepage.php', 'admincompanyrequest.php', 'adminviewcompany.php', 'adminviewuser.php', 'adminviewjobs.php', 'adminviewpendingrequest.php', 'allacceptedapplicant.php', 'allrejectedapplicant.php', 'manageusers.php', 'managecompanies.php', 'managejobs.php', 'adminupdatecompany.php?cid=1', 'adminupdateuser.php?uid=1', 'admineditjobs.php?job_id=1']:
                assert admin.request('/' + path)[0] == 200, path
            for path in ['companyhomepage.php', 'companyprofile.php', 'companyaddjobs.php', 'companyrecivedrequest.php', 'companyaccepteduser.php', 'companydeclinerequest.php', 'addjob.php']:
                assert company.request('/' + path)[0] == 200, path
            for path in ['userhomepage.php', 'userviewjobs.php', 'userprofile.php', 'userupdateprofile.php?uid=1', 'userjobrequeststatus.php', 'useraccepted.php', 'userrejected.php']:
                assert user.request('/' + path)[0] == 200, path
            assert outsider.request('/companydeletejob.php?id=1')[0] == 404
            assert admin.request('/admindeletejobs.php?job_id=1')[0] == 302
            assert sql('SELECT COUNT(*) FROM accepted_application') == '0'
            assert admin.request('/managecompanies.php?delete_id=1')[0] == 302
            assert sql('SELECT COUNT(*) FROM jobs') == '0' and sql('SELECT COUNT(*) FROM declined_application') == '0'
            assert sql('SELECT COUNT(*) FROM company') == '1'
            assert admin.request('/manageusers.php?delete_id=1')[0] == 302
            assert sql('SELECT COUNT(*) FROM user') == '0'
            user.request('/logout.php')
            assert user.request('/userhomepage.php')[0] == 302
            # Migrate an older, incomplete schema and preserve existing accounts.
            legacy = (ROOT / 'allsql.sql').read_text(encoding='utf-8')
            for table in ['accepted_application', 'declined_application']:
                legacy = re.sub(r'CREATE TABLE `' + table + r'` \([\s\S]+?\) ENGINE=InnoDB[^;]*;', '', legacy)
                legacy = re.sub(r'ALTER TABLE `' + table + r'`[^;]*;', '', legacy)
            legacy = legacy.replace('`user`', '`users`').replace('`experiences`', '`experience`')
            # Real older installations can contain nullable fields and numeric phones.
            legacy = legacy.replace('`skills` text NOT NULL', '`skills` text NULL')
            legacy = legacy.replace('`email` varchar(254) NOT NULL', '`email` varchar(254) NULL')
            legacy = legacy.replace('`phone` varchar(30) NOT NULL', '`phone` INT NULL')
            legacy = re.sub(r'(CREATE TABLE `jobapplication` \([\s\S]+?\) ENGINE=InnoDB[^;]*;)',
                            lambda match: re.sub(r'(`(?:fullname|address|experience|cv|photo)`[^\n]*)NOT NULL', r'\1NULL', match[0]), legacy)
            legacy = re.sub(r'  `(?:pan_image|company_logo|est_year|employees)`[^\n]*\n', '', legacy)
            legacy = legacy.replace('  `company_id` int(11) NOT NULL\n', '').replace('  `category` varchar(50) NOT NULL,\n)', '  `category` varchar(50) NOT NULL\n)')
            sql('CREATE DATABASE jfc_legacy CHARACTER SET utf8mb4', database=None)
            sql(legacy, database='jfc_legacy')
            sql("INSERT INTO company (company_name, username, password, email, address, company_pan, company_type, datecreated) VALUES ('Legacy Co','oldcompany','unused','old@example.com','Kathmandu','OLD','IT',NOW())", database='jfc_legacy')
            sql("INSERT INTO users (fname, lname, username, password, email, qualification, skills, gender, datecreated) VALUES ('Legacy','Seeker','oldseeker','unchanged-password',NULL,'Bachelor',NULL,'Other',NOW())", database='jfc_legacy')
            sql("INSERT INTO jobs (title, description, location, qualification, salary, image, username, openeddate, expirydate, category) VALUES ('Old PHP Job','PHP','Kathmandu','Bachelor','Negotiable','','oldcompany',NOW(),DATE_ADD(CURDATE(), INTERVAL 30 DAY),'IT & Software')", database='jfc_legacy')
            sql("INSERT INTO jobapplication (job_id, username, applied_date) VALUES (1,'oldseeker',NOW())", database='jfc_legacy')
            legacy_env = dict(env, DB_NAME='jfc_legacy')
            for _ in range(2):
                result = subprocess.run(['php', str(ROOT / 'migrate.php')], env=legacy_env, capture_output=True, creationflags=FLAGS)
                assert result.returncode == 0, result.stderr.decode()
            assert sql('SELECT COUNT(*) FROM company', database='jfc_legacy') == '1'
            assert sql('SELECT company_id FROM jobs WHERE id=1', database='jfc_legacy') == '1'
            assert sql('SELECT password FROM user WHERE uid=1', database='jfc_legacy') == 'unchanged-password'
            assert sql('SELECT skills FROM user WHERE uid=1', database='jfc_legacy') == ''
            assert sql('SELECT COUNT(*) FROM jobapplication', database='jfc_legacy') == '1'
            assert sql('SELECT phone FROM jobapplication WHERE id=1', database='jfc_legacy') == ''
            assert sql("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='jfc_legacy' AND TABLE_NAME IN ('user','accepted_application','declined_application')", database='jfc_legacy') == '3'
            # The exact same file is importable by mysql/phpMyAdmin and rerunnable.
            for _ in range(2):
                sql((ROOT / 'update_existing_database.sql').read_text(encoding='utf-8'), database='jfc_legacy')
            assert sql('SELECT COUNT(*) FROM jobapplication', database='jfc_legacy') == '1'
            assert sql('SELECT fname FROM user WHERE uid=1', database='jfc_legacy') == 'Legacy'
            assert sql("SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA='jfc_legacy' AND ROUTINE_NAME='jfc_update_existing_database'", database='jfc_legacy') == '0'
            print('PASS: login, role isolation, registration, custom skills, recommendations, applications, decisions, all dashboards, deletion, logout and legacy migrations.')
        finally:
            # Remove only the exact upload names recorded from this test database.
            for path in new_uploads:
                path.unlink()
            for process in reversed(processes):
                process.terminate()
                try:
                    process.wait(timeout=10)
                except subprocess.TimeoutExpired:
                    process.kill()
                    process.wait()
            db_log.close()
            php_log.close()
            log = (temp / 'php.log').read_text(errors='replace')
            if re.search(r'PHP (Fatal error|Warning|Deprecated|Notice|Parse error)', log):
                raise AssertionError(log[-5000:])


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--mysql-bin', type=Path, default=Path('C:/xampp/mysql/bin'))
    run(parser.parse_args().mysql_bin)
