<?php
function skill_list($input): array
{
    $values = is_array($input) ? $input : (is_string($input) ? explode(',', $input) : []);
    $skills = [];
    foreach ($values as $value) {
        if (!is_string($value)) continue;
        foreach (explode(',', $value) as $label) {
            $label = trim(preg_replace('/\s+/u', ' ', $label) ?? '');
            if ($label !== '') $skills[mb_strtolower($label, 'UTF-8')] ??= $label;
        }
    }
    return array_values($skills);
}

function submitted_skills(): array
{
    $selected = skill_list($_POST['skills'] ?? []);
    return skill_list(array_merge($selected, skill_list($_POST['custom_skills'] ?? '')));
}

function skill_validation_error(array $skills): string
{
    if (!$skills) return '*Add or select at least one skill';
    if (count($skills) > 50) return '*You can save up to 50 skills';
    foreach ($skills as $skill) {
        if (mb_strlen($skill, 'UTF-8') > 80) return '*Each skill must be 80 characters or fewer';
    }
    return '';
}

function render_skill_picker(array $selected): void
{
    $defaults = ['HTML', 'CSS', 'JavaScript', 'PHP', 'MySQL', 'Python', 'Java', 'C++', 'C#', 'Ruby', 'Rust', 'Dart', 'TypeScript', 'WordPress', 'Flutter', 'Kubernetes', 'Communication', 'Customer Service', 'Other'];
    $keys = array_map(fn($skill) => mb_strtolower($skill, 'UTF-8'), $selected);
    foreach (skill_list(array_merge($defaults, $selected)) as $skill) {
        $label = htmlspecialchars($skill, ENT_QUOTES, 'UTF-8');
        $checked = in_array(mb_strtolower($skill, 'UTF-8'), $keys, true) ? ' checked' : '';
        echo "<label class='selection-label-item' style='display:inline-flex;gap:6px;align-items:center;margin:5px 10px 5px 0'><input type='checkbox' name='skills[]' value='$label'$checked> $label</label>";
    }
    echo '<div style="width:100%;margin-top:12px"><label for="custom_skills">Add your own skills</label><input id="custom_skills" name="custom_skills" type="text" class="form-input" placeholder="e.g., Excel, Graphic Design, Accounting" aria-describedby="custom_skills_help"><small id="custom_skills_help">Separate skills with commas. Up to 50 skills, 80 characters each. Uncheck a saved skill to remove it.</small></div>';
}

function matching_job_skills(array $job, array $skills): array
{
    $text = implode(' ', [$job['title'], $job['description'], $job['qualification'], $job['category']]);
    $matches = [];
    foreach ($skills as $skill) {
        if (mb_strtolower($skill, 'UTF-8') === 'other') continue;
        // Boundaries keep Java separate from JavaScript and C++ separate from C.
        $pattern = '/(?<![\p{L}\p{N}_+#])' . preg_quote($skill, '/') . '(?![\p{L}\p{N}_+#])/iu';
        if (preg_match($pattern, $text)) $matches[] = $skill;
    }
    return $matches;
}

function related_jobs(mysqli $con, array $skills, ?string $category = null): array
{
    if (!$skills) return [];
    $sql = 'SELECT * FROM jobs WHERE expirydate >= CURDATE()';
    if ($category !== null && $category !== '' && $category !== 'all') $sql .= ' AND category = ?';
    $stmt = $con->prepare($sql . ' ORDER BY openeddate DESC, id DESC');
    if ($category !== null && $category !== '' && $category !== 'all') $stmt->bind_param('s', $category);
    $stmt->execute();
    $jobs = [];
    foreach ($stmt->get_result() as $job) {
        $job['matched_skills'] = matching_job_skills($job, $skills);
        if ($job['matched_skills']) $jobs[] = $job;
    }
    usort($jobs, fn($a, $b) => count($b['matched_skills']) <=> count($a['matched_skills']) ?: strcmp($b['openeddate'], $a['openeddate']) ?: $b['id'] <=> $a['id']);
    return $jobs;
}
