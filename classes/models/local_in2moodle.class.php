<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once(dirname(__FILE__) . '/../../config.php');

/**
 * Integration in to Moodle plataform.
 *
 * @copyright  2016 Planificaci�n Entornos Tecnol�gicos {@link http://www.pentec.es/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_in2moodle {

    /**
     * Connection data
     * @var object $connectdata
     */
    private $connectdata;

    /**
     * Configuration object of the plugin.
     * Define what data has to be transfered.
     * @var object $eudeconfig
     */
    private $config;

    /**
     * Format of input file (default json).
     * @var string $inputformat
     */
    private $inputformat = 'json';

    /**
     * Input plataform (Ej Educativa, Blackboard, etc).
     * @var string $inputplataform
     */
    private $inputplataform;

    /**
     *  Main method.
     */
    public function in2moodle () {
        global $CFG;

        $this->load_connection_data();
        $this->load_data_configuration();
        $this->get_input_data();
        $this->create_new_structure();
        $this->process_input_data();
        $this->create_output_report();
        $this->show_output_report();
    }

    /**
     * Create an assign into the moodle course
     * @param int $gradecategory Gradecategory id
     *
     * @return boolean Indicate if the process has been succeeded
     */
    private function create_moodle_assign ($assignpluginconfigoptions, $categoryid, $courseid, $name, $intro, $introformat = 1,
            $alwaysshowdescription = 1, $nosubmission = 0, $submissiondrafts = 0, $sendnotifications = 0,
            $sendlatenotifications = 0, $duedate = 0, $allowsubmissionsfromdate = 0, $grade = 100, $timemodified = 0,
            $requiresubmissionstatement = 0, $completionsubmit = 0, $cutoffdate = 0, $teamsubmission = 0,
            $requireallteammemberssubmit = 0, $teamsubmissiongroupingid = 0, $blindmarking = 0, $revealidentities = 0,
            $attemptreopenmethod = '', $maxattempts = 1, $markingworkflow = 0, $markingallocation = 0, $sendstudentnotifications = 1) {

        global $DB;

        $integrated = false;

        try {
            $transaction = $DB->start_delegated_transaction();
            // Add assign and return assignid.
            $assign = new stdClass();
            $assign->course = $courseid;
            $assign->name = $ename;
            $assign->intro = $intro;
            $assign->introformat = $introformat;
            $assign->alwaysshowdescription = 1;
            $assign->nosubmission = $nosubmission;
            $assign->submissiondrafts = $submissiondrafts;
            $assign->sendnotifications = $sendnotifications;
            $assign->sendlatenotifications = $sendlatenotifications;
            $assign->duedate = $duedate;
            $assign->allowsubmissionsfromdate = $allowsubmissionsfromdate;
            $assign->grade = $grade;
            $assign->timemodified = $timemodified;
            $assign->requiresubmissionstatement = $requiresubmissionstatement;
            $assign->completionsubmit = $completionsubmit;
            $assign->cutoffdate = $cutoffdate;
            $assign->teamsubmission = $teamsubmission;
            $assign->requireallteammemberssubmit = $requireallteammemberssubmit;
            $assign->teamsubmissiongroupingid = $teamsubmissiongroupingid;
            $assign->blindmarking = $blindmarking;
            $assign->revealidentities = $revealidentities;
            $assign->attemptreopenmethod = $attemptreopenmethod;
            $assign->maxattempts = $maxattempts;
            $assign->markingworkflow = $markingworkflow;
            $assign->markingallocation = $markingallocation;
            $assign->sendstudentnotifications = $sendstudentnotifications;

            $assignid = $DB->insert_record('assign', $assign);

            if (!$assignid) {
                throw new Exception(get_string('assignerror', 'local_in2moodle') . ' ' . $name . ' in the course ' . $courseid);
            }

            // Once we create the assing, we create the objects related to it.
            // We create the grade item related to the assign.
            $assigngradeitem = new stdClass();
            $assigngradeitembase->courseid = $courseid;
            $assigngradeitembase->categoryid = $categoryid;
            $assigngradeitembase->itemtype = "mod";
            $assigngradeitembase->itemmodule = "assign";
            $assigngradeitembase->iteminstance = $assignid;
            $assigngradeitembase->itemnumber = 0;
            $assigngradeitembase->gradetype = 1;
            $assigngradeitembase->grademax = $grade;
            $assigngradeitembase->grademin = 0.00000;
            $assigngradeitemid = $DB->insert_record('grade_items', $gradeitembase);

            if (!$assigngradeitemid) {
                throw new Exception(get_string('assignerror', 'local_in2moodle') . ' ' . $name . ' in the course ' . $courseid);
            }

            // We create the entries with the settings of the assign in assign_plugin_config table.
            foreach ($assignpluginconfigoptions as $assignpluginconfigoption) {
                $assignpluginconfig = new stdClass();
                $assignpluginconfig->assignment = $assignid;
                $assignpluginconfig->plugin = $assignpluginconfigoption->plugin;
                $assignpluginconfig->subtype = $assignpluginconfigoption->subtype;
                $assignpluginconfig->name = $assignpluginconfigoption->name;
                $assignpluginconfig->value = $assignpluginconfigoption->value;

                $assignpluginconfig = $DB->insert_record('assign_plugin_config', $assignpluginconfig);

                if (!$assignpluginconfig) {
                    throw new Exception(get_string('assignerror', 'local_in2moodle') . ' ' . $name . ' in the course ' . $courseid);
                }
            }

            // We create a new event for the assign if the duedate is active.
            if ($assign->duedate) {
                $event = new stdClass();
                $event->name = $assign->name;
                $event->description = $assign->intro;
                $event->format = $assign->introformat;
                $event->courseid = $assign->course;
                $event->groupid = 0;
                $event->userid = 0;
                $event->modulename = 'assign';
                $event->instance = $assignid;
                $event->eventtype = 'due';
                $event->timestart = $assign->duedate;
                $event->timeduration = 0;
                $event->visible = 1;
                $eventcreated = calendar_event::create($event);

                if (!$eventcreated) {
                    throw new Exception(get_string('assignerror', 'local_in2moodle') . ' ' . $name . ' in the course ' . $courseid);
                }
            }
            $transaction->allow_commit();
            $integrated = true;
        } catch (Exception $e) {
            $transaction->rollback($e);
            $integrated = false;
        } finally {
            return $integrated;
        }
    }

    public function create_moodle_quiz($name, $course, $questions = NULL, $intro = '', $introformat = 1, $timeopen = 0,
            $timeclose = 0, $timelimit = 0, $overduehandling = 'autosubmit', $graceperiod = 0, $preferredbehaviour = 'adaptive',
            $canredoquestions = 0, $attempts = 0, $attemptonlast = 0, $grademethod = 1, $decimalpoints = 0,
            $questiondecimalpoints = -1, $reviewattempt = 65536, $reviewcorrectness = 0, $reviewmarks = 0,
            $reviewspecificfeedback = 0, $reviewgeneralfeedback = 0, $reviewrightanswer = 0, $reviewoverallfeedback = 0,
            $questionsperpage = 0, $navmethod = 'free', $shuffleanswers = 0, $sumgrades = 1, $grade = 100,
            $timecreated = 0, $timemodified = 0, $password = '', $subnet = '', $browsersecurity = '-', $delay1 = 0,
            $delay2 = 0, $showuserpicture = 0, $showblocks = 0, $completionattemptsexhausted = 0, $completionpass = 0) {
        
        global $DB;
        
        $integrated = false;

        try {
            $transaction = $DB->start_delegated_transaction();
            // Add quiz and return quizid.
            $quiz = stdClass();
            $quiz->name = $name;
            $quiz->course = $course;
            $quiz->intro = $intro;
            $quiz->introformat = $introformat;
            $quiz->timeopen = $timeopen;
            $quiz->timeclose = $timeclose;
            $quiz->timelimit = $timelimit;
            $quiz->overduehandling = $overduehandling;
            $quiz->graceperiod = $graceperiod;
            $quiz->preferredbehaviour = $preferredbehaviour;
            $quiz->canredoquestions = $canredoquestions;
            $quiz->attempts = $attempts;
            $quiz->attemptonlast = $attemptonlast;
            $quiz->grademethod = $grademethod;
            $quiz->decimalpoints = $decimalpoints;
            $quiz->questiondecimalpoints = $questiondecimalpoints;
            $quiz->reviewattempt = $reviewattempt;
            $quiz->reviewcorrectness = $reviewcorrectness;
            $quiz->reviewmarks = $reviewmarks;
            $quiz->reviewspecificfeedback = $reviewspecificfeedback;
            $quiz->reviewgeneralfeedback = $reviewgeneralfeedback;
            $quiz->reviewrightanswer = $reviewrightanswer;
            $quiz->reviewoverallfeedback = $reviewoverallfeedback;
            $quiz->questionsperpage = $questionsperpage;
            $quiz->navmethod = $navmethod;
            $quiz->shuffleanswers = $shuffleanswers;
            $quiz->sumgrades = $sumgrades;
            $quiz->grade = $grade;
            $quiz->timecreated = $timecreated;
            $quiz->timemodified = $timemodified;
            $quiz->password = $password;
            $quiz->subnet = $subnet;
            $quiz->browsersecurity = $browsersecurity;
            $quiz->delay1 = $delay1;
            $quiz->delay2 = $delay2;
            $quiz->showuserpicture = $showuserpicture;
            $quiz->showblocks = $showblocks;
            $quiz->completionattemptsexhausted = $completionattemptsexhausted;
            $quiz->completionpass = $completionpass;

            //quiz_add_instance($quiz);

            $quizid = $DB->insert_record('quiz', $quiz);
            
            $coursedata = $DB->get_record('course', array('id'=>$course));
            
            if (!$quizid) {
                throw new Exception(get_string('assignerror', 'local_in2moodle') . ' ' . $name . ' in the course ' . $course);
            }
            
            // Create the grade item related to the quiz.
            $quizgradeitem = new stdClass();
            $quizgradeitem->courseid = $course;
            $quizgradeitem->categoryid = $coursedata->category;
            $quizgradeitem->itemtype = "mod";
            $quizgradeitem->itemmodule = "quiz";
            $quizgradeitem->iteminstance = $quizid;
            $quizgradeitem->itemnumber = 0;
            $quizgradeitem->gradetype = 1;
            $quizgradeitem->grademax = $grade;
            $quizgradeitem->grademin = 0.00000;
            $gradeitemid = $DB->insert_record('grade_items', $quizgradeitem);

            if (!$gradeitemid) {
                throw new Exception(get_string('assignerror', 'local_in2moodle') . ' ' . $name . ' in the course ' . $courseid);
            }
            
            // Create quiz section.
            $quizsection = new stdClass();
            $quizsection->quizid = $quizid;
            $quizsection->firstslot = 1;
            $quizsection->heading = '';
            $quizsection->shufflequestions = 0;
            $quizsectionid = $DB->insert_record('quiz_sections', $quizsection);
            
            // Create quiz feedback.
            $quizfeedback = new stdClass();
            $quizfeedback->quizid = $quizid;
            $quizfeedback->feedbacktext = '';
            $quizfeedback->feedbacktextformat = 1;
            $quizfeedback->mingrade = 0;
            $quizfeedback->maxgrade = $grade * 1.01;//No está muy claro de dónde saca el valor
            
            // Create course module for this quiz.
            $coursemodulequiz = new stdClass();
            $coursemodulequiz->course = $course;
            $coursemodulequiz->module = 16;
            $coursemodulequiz->instance = $quizid;
            $coursemodulequiz->section = $quizsectionid;
            $coursemodulequiz->added = $timecreated;
            $coursemodulequiz->score = 0;
            $coursemodulequiz->indent = 0;
            $coursemodulequiz->visible = 1;
            $coursemodulequiz->visibleold = 1;
            $coursemodulequiz->groupmode = 0;
            $coursemodulequiz->groupingid = 0;
            $coursemodulequiz->completion = 0;
            $coursemodulequiz->completionview = 0;
            $coursemodulequiz->completionexpected = 0;
            $coursemodulequiz->showdescription = 0;
            $coursemoduleid = $DB->insert_record('course_module', $coursemodulequiz);
            
            // Add Instance.
            $quiz->coursemodule = $coursemoduleid;
            $quiz->feedbacktext = '';
            $quiz->cmidnumber = $coursemoduleid;
            $instance = quiz_add_instance($quiz);
            
            
            // Add questions if have questions.
            if ($questions) {
                
                //$slot = 1;
                foreach ($questions as $question) {
                    
                    quiz_add_quiz_question($question->id, $quizid, $question->page);
                           /* 
                    $quizslot = new stdClass();
                    $quizslot->slot = $slot;
                    $quizslot->quizid = $quizid;
                    $quizslot->page = $question->page;
                    $quizslot->requireprevious = 0;
                    $quizslot->questionid = $question->id;
                    $quizslot->maxmark = 1;
                    $quizslotid = $DB->insert_record('quiz_slot', $quizslot);
                            * 
                            */
                    //$slot ++;
                }
                
            }
        }
            
    }
  
}
