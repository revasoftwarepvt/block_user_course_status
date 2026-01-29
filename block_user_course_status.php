<?php
defined('MOODLE_INTERNAL') || die();

class block_user_course_status extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_user_course_status');
    }

    public function applicable_formats() {
        return ['my' => true]; // Dashboard only
    }

    public function get_content() {
        global $CFG,$USER,$PAGE,$DB;
		
		$PAGE->requires->js_call_amd('block_user_course_status/block', 'init');
//$PAGE->requires->css('https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css');
		$PAGE->requires->css(new moodle_url(
    'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css'
));

        if ($this->content !== null) {
            return $this->content;
        }

        require_once($CFG->libdir . '/completionlib.php');
		
        $this->content = new stdClass();
        $this->content->text = '';
		/*
		$this->content->text .= '
<div class="course-status-filters mb-2">
    <select id="course-category" class="form-select form-select-sm">
        <option value="">All Categories</option>';
		
		$categories = core_course_category::make_categories_list();
foreach ($categories as $id => $name) {
    $this->content->text .= '<option value="'.$id.'">'.$name.'</option>';
}

$this->content->text .= '
    </select>

    <input type="text" id="course-search"
        class="form-control form-control-sm mt-2"
        placeholder="Search course">
</div>';
*/
        $courses = enrol_get_users_courses($USER->id, true);

        if (!$courses) {
            $this->content->text = get_string('nocourses', 'moodle');
            return $this->content;
        }

        $this->content->text .= '<table id="mydatatable" class="generaltable">';
        $this->content->text .= '
            <thead>
                <tr class="course-row"
    data-category="'.$course->category.'"
    data-name="'.strtolower($course->fullname).'">
                    <th>' . get_string('course', 'block_user_course_status') . '</th>
					<th>' . get_string('category', 'block_user_course_status') . '</th>
                    <th>' . get_string('status', 'block_user_course_status') . '</th>
					<th align="center">' . get_string('module_count', 'block_user_course_status') . '</th>
                    <th>' . get_string('progress', 'block_user_course_status') . '</th>
                </tr>
            </thead><tbody>';

        foreach ($courses as $course) {

            $status = get_string('enrolled', 'block_user_course_status');
            $progress = 0;
            $class = 'status-notstarted';

            //if ($course->enablecompletion) {

                $completion = new completion_info($course);
                $activities = $completion->get_activities();

                $total = 0;
                $done = 0;

                foreach ($activities as $activity) {
                    if ($completion->is_enabled($activity)) {
                        $total++;
                        $data = $completion->get_data($activity, false, $USER->id);
                        if ($data->completionstate == COMPLETION_COMPLETE) {
                            $done++;
                        }
                    }
                }
				
				$vLastAccess = $DB->count_records('user_lastaccess',array("userid"=>$USER->id,"courseid"=>$course->id));

                if ($total > 0) {
                    $progress = round(($done / $total) * 100);
                }

                if ($progress == 0 && $vLastAccess == 0) {
                    $status = get_string('notstarted', 'block_user_course_status');
                } else if ($progress < 100 and $vLastAccess > 0) {
                    $status = get_string('incomplete', 'block_user_course_status');
                    $class = 'status-incomplete';
                } else {
                    $status = get_string('completed', 'block_user_course_status');
                    $class = 'status-completed';
                }
            //}
$vCategoryName = $DB->get_field('course_categories','name',array("id"=>$course->category));
            $this->content->text .= '
                <tr>
                    <td><a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">' . format_string($course->fullname) . '</a></td>
					<td class="' . $class . '">' . $vCategoryName . '</td>
                    <td class="' . $class . '">' . $status . '</td>
					<td class="' . $class . '">' . $done.'/'.$total . '</td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar"
                                style="width:' . $progress . '%">
                                ' . $progress . '%
                            </div>
                        </div>
                    </td>
                </tr>';
        }

        $this->content->text .= '</tbody></table>';
		
		
	
        return $this->content;
    }
}
