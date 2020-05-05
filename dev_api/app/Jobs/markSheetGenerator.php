<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models2\Examterms;
use App\Models2\ExamsList;
use App\Models2\exam_marks;
use App\Models2\MClass;
use App\Models2\Main;
use App\Models2\MarkSheet;
use App\Models2\MarkSheetStudents;
use App\Models2\SchoolTerm;
use App\Models2\SubSubject;
use App\Models2\Subject;
use App\Models2\Section;
use App\Models2\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Bus\Queueable;
use Exception;

class markSheetGenerator extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    public $userId;
    public $classId;
    public $sectionId;
    public $selectAcYear;
    public $getSelections;
    public $currentSheetId;

    public function __construct( $userId, $classId, $sectionId, $getSelections, $selectAcYear )
    {
        $this->userId = $userId;
        $this->classId = $classId;
        $this->sectionId = $sectionId;
        $this->selectAcYear = $selectAcYear;
        $this->getSelections = $getSelections;
    }

    public function handle()
    {
        User::$withoutAppends = true;
        $userId = $this->userId;
        $classId = $this->classId;
        $sectionId = $this->sectionId;
        $selectAcYear = $this->selectAcYear;
        $getSelections = $this->getSelections;
        $settings = [];
        $settings['grades'] = $getSelections['grades'];
        $settings['passMarks'] = $getSelections['passMarks'];
        $dbTerms = SchoolTerm::select('id', 'title')->get()->toArray();
        foreach( $dbTerms as $oneTerm ) { $termId = intval( $oneTerm['id'] ); $terms[$termId] = $oneTerm; }
        $examList = ExamsList::select('id', 'examTitle as name', 'examSchedule', 'school_term_id as term')->where('examAcYear', $selectAcYear)->get()->toArray();
        foreach( $examList as $oneExam ) { $id = intval($oneExam['id']); $exams_list[$id] = $oneExam; }
        $subjects = Subject::get();
        $subSubjects = SubSubject::get();
		foreach( $subjects as $subject ) { $subject_id = intval($subject->id); $mainSubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }
        foreach( $subSubjects as $subject ) { $subject_id = intval($subject->id); $secondarySubjects[$subject_id] = ['id' => $subject_id, 'name' => $subject->subjectTitle]; }
        
        $db_exams = ExamsList::select('id', 'examTitle as name', 'examSchedule as schedule')->get();
        foreach( $db_exams as $oneExam )
        {
            $examSchedule = [];
            $examId = intval( $oneExam->id );
            $schedule = json_decode($oneExam->schedule, true);
            if( json_last_error() != JSON_ERROR_NONE ) $schedule = [];
            foreach( $schedule as $key => $oneSchedule )
            {
                if( isset($oneSchedule['subject_type']) )
                {
                    $oneSubjectId = $oneSchedule['subject_type'];
                    if( !is_numeric($oneSubjectId) ) continue;
                    else
                    {
                        if( substr($oneSubjectId, 0, 2) == "m_" || substr($oneSubjectId, 0, 2) == "s_" ) continue;
                        if( $oneSchedule['subject_type'] == "main" ) $schedule[$key]['subject'] = "m_" . $oneSchedule['subject'];
                        elseif( $oneSchedule['subject_type'] == "secondary" ) $schedule[$key]['subject'] = "s_" . $oneSchedule['subject'];
                    }
                }
                else
                {
                    $schedule[$key]['subject'] = "m_" . $oneSchedule['subject'];
                }
                unset( $schedule[$key]['stDate'] );
                unset( $schedule[$key]['subject_type'] );
                unset( $schedule[$key]['teachers'] );
                unset( $schedule[$key]['start_time'] );
                unset( $schedule[$key]['end_time'] );
                if( !isset( $oneSchedule['pass_marks'] ) ) $schedule[$key]['pass_marks'] = 0;
                if( !isset( $oneSchedule['max_marks'] ) ) $schedule[$key]['max_marks'] = 0;
            }
            foreach( $schedule as $key => $oneSchedule )
            {
                if( !array_key_exists('subject', $oneSchedule) ) continue;
                $subject = $oneSchedule['subject'];
                $max = isset($oneSchedule['max_marks']) ? $oneSchedule['max_marks'] : 0;
                $pass = isset($oneSchedule['pass_marks']) ? $oneSchedule['pass_marks'] : 0;
                $examSchedule[$subject] = ['pass' => $pass, 'max' => $max];
            }

            $loadedExams[$examId] = ['id' => $examId, 'name' => $oneExam->name, 'schedule' => $examSchedule];
        }
        
        $topRight[0] = [ "school" => $getSelections['topRight']['school'], "title" => $getSelections['topRight']['title'] ];
        $topLeft = [];
        foreach( $getSelections['topLeft'] as $key => $value ) { if( $value == true ) $topLeft[] = $key; }
        $examsList = [];
        $header['rows'][0]['payload'][0] = ["name" => "Subjects", "colspan" => 1, "rowspan" => 2 ];
        $header['rows'][1]['payload'] = [];

        foreach( $getSelections['exams'] as $key => $exam )
        {
            if( !$exam || !is_array($exam) || $exam == null ) unset( $getSelections['exams'][$key] );
            else
            {
                if( $exam['choosen'] == true ) $examsList[] = $exam;
            }
        }
        foreach( $examsList as $oneTerm )
        {
            $termId = intval( $oneTerm['id'] );
            if( isset( $oneTerm['customName'] ) )
            {
                if( trim( $oneTerm['customName'] ) ) $colName = $oneTerm['customName'];
                else { $colName = $terms[$termId]['title']; }
            } else { $colName = $terms[$termId]['title']; }
            $colspan = count( $oneTerm['exams'] );
            $header['rows'][0]['payload'][] = ["termId" => $termId, "name" => $colName, "colspan" => $colspan, "rowspan" => 1, "outOf" => $oneTerm['outOf'], "choosen" => $oneTerm['choosen'], "total" => $oneTerm['total'], "average" => $oneTerm['average'], "grade" => $oneTerm['grade'] ];
            foreach( $oneTerm['exams'] as $oneExam )
            {
                $examId = intval( $oneExam['id'] );
                if( isset( $oneExam['customName'] ) )
                {
                    if( trim( $oneExam['customName'] ) ) $col2Name = $oneExam['customName'];
                    else { $col2Name = $exams_list[$examId]['name']; }
                } else { $col2Name = $exams_list[$examId]['name']; }
                $header['rows'][1]['payload'][] = ["examId" => $examId, "termId" => $termId, "name" => $col2Name, "colspan" => 1, "rowspan" => 1, "outOf" => $oneExam['outOf'], "choosen" => $oneExam['choosen'], "total" => $oneTerm['total'], "average" => $oneTerm['average'], "grade" => $oneTerm['grade'] ];
            }
        }
        foreach( $getSelections['rows'] as $key => $rowItem )
        {
            if( trim( $rowItem['subjectName'] ) ) continue;
            $mixedKey = $rowItem['subjectId'];
            $parse = explode('_', $mixedKey);
            if( count($parse) < 2 ) continue;
            $type = $parse[0];
            $subjectId = intval( $parse[1] );
            if( !trim( $rowItem['subjectName'] ) )
            {
                $subjectName = $type == "m" ? $mainSubjects[$subjectId]['name'] : $secondarySubjects[$subjectId]['name'];
                $getSelections['rows'][$key]['subjectName'] = $subjectName;
            }
            $getSelections['rows'][$key]['cells'] = [];
        }
        $body['rows'] = []; $termConfig = [];
        foreach( $getSelections['rows'] as $key => $oneRow )
        {
            $currentTerm = 0;
            foreach( $header['rows'][1]['payload'] as $innerKey => $data )
            {
                $examId = intval( $data['examId'] );
                $termId = intval( $data['termId'] );
                $outOf = $data['outOf'];
                $getSelections['rows'][$key]['cells'][$termId][] = [ 'exam' => $examId, "type" => "normal", "outOf" => $outOf ];
                $termConfig[$termId] = [ 'total' => $data['total'], 'average' => $data['average'], 'grade' => $data['grade'], "outOf" => $outOf ];
            }
        }
        foreach( $getSelections['rows'] as $key => $oneRow )
        {
            foreach( $oneRow['cells'] as $termId => $oneCell )
            {
                $counter = 0;
                foreach( $oneCell as $cell )
                {
                    $counter = $counter + 1;
                    if( $counter == count($oneRow['cells'][$termId]))
                    {
                        if( $termConfig[$termId]['total'] == true ) $getSelections['rows'][$key]['cells'][$termId][] = ["type" => "totalCell"];
                        if( $termConfig[$termId]['average'] == true ) $getSelections['rows'][$key]['cells'][$termId][] = ["type" => "averageCell"];
                        if( $termConfig[$termId]['grade'] == true ) $getSelections['rows'][$key]['cells'][$termId][] = ["type" => "gradeCell"];
                    }
                }
            }
        }
        $body['rows'] = $getSelections['rows'];
        $footer['rows'] = [];
        foreach( $getSelections['footer'] as $key => $footerRow )
        {
            foreach( $footerRow['cells'] as $footerCell )
            {
                $type = $footerCell['type'];
                if( $type == "cellName" ) $footer['rows'][$key]['title'] = $footerCell['customName'];
                else
                {
                    if( $type == "total" || $type == "percent" || $type == "rank" )
                    {
                        $footer['rows'][$key]['cells'][] = [
                            'type' => $type, 'term' => intval( $footerCell['customName'] ),
                            'colspan' => $footerCell['colspan'], 'rowspan' => $footerCell['rowspan']
                        ];
                    }
                    else
                    {
                        $footer['rows'][$key]['cells'][] = [
                            'type' => $type, 'colspan' => $footerCell['colspan'], 'rowspan' => $footerCell['rowspan']
                        ];
                    }
                }
            }
        }
        foreach( $header['rows'][0]['payload'] as $key => $value )
        {
            if( $key != 0 )
            {
                $colspan = $header['rows'][0]['payload'][$key]['colspan'];
                if( $value['total'] == true ) { $colspan = $colspan + 1; }
                if( $value['average'] == true ) { $colspan = $colspan + 1; }
                if( $value['grade'] == true ) { $colspan = $colspan + 1; }
                $header['rows'][0]['payload'][$key]['colspan'] = $colspan;
            }
        }
        if( $getSelections['cumulative']['choosen'] == true )
        {
            $header['rows'][0]['payload'][] = [
                "name" => $getSelections['cumulative']['customName'],
                "colspan" => 1,
                "rowspan" => 2,
                "outOf" => $getSelections['cumulative']['outOf'],
                "choosen" => true,
                "total" => false,
                "average" => false,
                "grade" => false,
                "speicalType" => "cumm"
            ];
            $settings['cumulative'] = $getSelections['cumulative'];
        } else $settings['cumulative'] = [];
        $newHeaderRows = []; $headerTerms = []; $compactedHeaders = [];
        foreach( $header['rows'][1]['payload'] as $key => $value )
        {
            $termId = intval($value['termId']);
            $compactedHeaders[$termId][] = $value;
            $headerTerms[$termId]['total'] = $value['total'];
            $headerTerms[$termId]['average'] = $value['average'];
            $headerTerms[$termId]['grade'] = $value['grade'];
        }
        foreach( $compactedHeaders as $termId => $headers )
        {
            foreach( $headers as $headTh ) { $newHeaderRows[] = $headTh; }
            if( $headerTerms[$termId]['total'] == true ) { $newHeaderRows[] = ["type" => "totalCell"]; }
            if( $headerTerms[$termId]['average'] == true ) { $newHeaderRows[] = ["type" => "averageCell"]; }
            if( $headerTerms[$termId]['grade'] == true ) { $newHeaderRows[] = ["type" => "gradeCell"]; }
        }
        $header['rows'][1]['payload'] = $newHeaderRows;
        $payload = [ "topRight" => $topRight, "topLeft" => $topLeft, "header" => $header, "body" => $body, "footer" => $footer, "settings" => $settings ];
        $sheet = new MarkSheet();
        $sheet->sheetName = $getSelections['topRight']['title'];
        $sheet->class_id = $classId ? $classId : 0;
        $sheet->section_id = $sectionId ? $sectionId : 0;
        $sheet->payload = json_encode( $payload );
        $sheet->status = "created";
        $sheet->created_by = $userId;
        $sheet->comments = NULL;
        $sheet->is_speical = "no";
        $sheet->created_at = date('Y-m-d H:i:s');
        $sheet->updated_at = date('Y-m-d H:i:s');
        $sheet->save();
        $sheetId = $sheet->id;
        $this->currentSheetId = $sheetId;

        $generatedSheet = MarkSheet::find($sheetId);
        $generatedSheet->status = "processing";
        $generatedSheet->save();
        
        $students = User::where('studentClass', $classId)->where('studentSection', $sectionId)->pluck('id');
        $payload = json_decode($generatedSheet->payload, true);
        $studentRanks = []; $studentPayload = [];
        $db_exams = ExamsList::select('id', 'examTitle as name', 'examSchedule as schedule')->get();
        foreach( $db_exams as $oneExam )
        {
            $examSchedule = [];
            $examId = intval( $oneExam->id );
            $schedule = json_decode($oneExam->schedule, true);
            if( json_last_error() != JSON_ERROR_NONE ) $schedule = [];
            foreach( $schedule as $key => $oneSchedule )
            {
                if( isset($oneSchedule['subject_type']) )
                {
                    $oneSubjectId = $oneSchedule['subject_type'];
                    if( !is_numeric($oneSubjectId) ) continue;
                    else
                    {
                        if( substr($oneSubjectId, 0, 2) == "m_" || substr($oneSubjectId, 0, 2) == "s_" ) continue;
                        if( $oneSchedule['subject_type'] == "main" ) $schedule[$key]['subject'] = "m_" . $oneSchedule['subject'];
                        elseif( $oneSchedule['subject_type'] == "secondary" ) $schedule[$key]['subject'] = "s_" . $oneSchedule['subject'];
                    }
                }
                else
                {
                    $schedule[$key]['subject'] = "m_" . $oneSchedule['subject'];
                }
                unset( $schedule[$key]['stDate'] );
                unset( $schedule[$key]['subject_type'] );
                unset( $schedule[$key]['teachers'] );
                unset( $schedule[$key]['start_time'] );
                unset( $schedule[$key]['end_time'] );
                if( !isset( $oneSchedule['pass_marks'] ) ) $schedule[$key]['pass_marks'] = 0;
                if( !isset( $oneSchedule['max_marks'] ) ) $schedule[$key]['max_marks'] = 0;
            }
            foreach( $schedule as $key => $oneSchedule )
            {
                if( !array_key_exists('subject', $oneSchedule) ) continue;
                $subject = $oneSchedule['subject'];
                $max = isset($oneSchedule['max_marks']) ? $oneSchedule['max_marks'] : 0;
                $pass = isset($oneSchedule['pass_marks']) ? $oneSchedule['pass_marks'] : 0;
                $examSchedule[$subject] = ['pass' => $pass, 'max' => $max];
            }

            $loadedExams[$examId] = ['id' => $examId, 'name' => $oneExam->name, 'schedule' => $examSchedule];
        }
        $students = User::where('studentClass', $classId)->where('studentSection', $sectionId)->pluck('id');
        $payload = json_decode($generatedSheet->payload, true);
        $studentRanks = []; $studentPayload = [];
        $db_exams = ExamsList::select('id', 'examTitle as name', 'examSchedule as schedule')->get();
        foreach( $db_exams as $oneExam )
        {
            $examSchedule = [];
            $examId = intval( $oneExam->id );
            $schedule = json_decode($oneExam->schedule, true);
            if( json_last_error() != JSON_ERROR_NONE ) $schedule = [];
            foreach( $schedule as $key => $oneSchedule )
            {
                if( isset($oneSchedule['subject_type']) )
                {
                    $oneSubjectId = $oneSchedule['subject_type'];
                    if( !is_numeric($oneSubjectId) ) continue;
                    else
                    {
                        if( substr($oneSubjectId, 0, 2) == "m_" || substr($oneSubjectId, 0, 2) == "s_" ) continue;
                        if( $oneSchedule['subject_type'] == "main" ) $schedule[$key]['subject'] = "m_" . $oneSchedule['subject'];
                        elseif( $oneSchedule['subject_type'] == "secondary" ) $schedule[$key]['subject'] = "s_" . $oneSchedule['subject'];
                    }
                }
                else
                {
                    $schedule[$key]['subject'] = "m_" . $oneSchedule['subject'];
                }
                unset( $schedule[$key]['stDate'] );
                unset( $schedule[$key]['subject_type'] );
                unset( $schedule[$key]['teachers'] );
                unset( $schedule[$key]['start_time'] );
                unset( $schedule[$key]['end_time'] );
                if( !isset( $oneSchedule['pass_marks'] ) ) $schedule[$key]['pass_marks'] = 0;
                if( !isset( $oneSchedule['max_marks'] ) ) $schedule[$key]['max_marks'] = 0;
            }
            foreach( $schedule as $key => $oneSchedule )
            {
                if( !array_key_exists('subject', $oneSchedule) ) continue;
                $subject = $oneSchedule['subject'];
                $max = isset($oneSchedule['max_marks']) ? $oneSchedule['max_marks'] : 0;
                $pass = isset($oneSchedule['pass_marks']) ? $oneSchedule['pass_marks'] : 0;
                $examSchedule[$subject] = ['pass' => $pass, 'max' => $max];
            }

            $loadedExams[$examId] = ['id' => $examId, 'name' => $oneExam->name, 'schedule' => $examSchedule];
        }
        $students = User::where('studentClass', $classId)->where('studentSection', $sectionId)->pluck('id');
        $studentRanks = []; $studentPayload = []; $termsConfig = [];
        foreach( $students as $studentId )
        {
            $termsConfig[$studentId] = [];
            $studentPayload[$studentId]['header'] = $payload['header'];
            $studentPayload[$studentId]['body'] = $payload['body'];
            $studentPayload[$studentId]['footer'] = $payload['footer'];

            foreach( $studentPayload[$studentId]['body']['rows'] as $index => $row )
            {
                foreach( $row['cells'] as $termId => $exam )
                {
                    if(!isset($termsConfig[$studentId][$termId])) $termsConfig[$studentId][$termId] = ['total' => 0, 'max' => 0, 'outOf' => 0];
                }
            }

            foreach( $studentPayload[$studentId]['header']['rows'][0]['payload'] as $index => $row )
            {
                if( $index == 0 ) continue;
                if( array_key_exists('speicalType', $row) ) continue;
                if( !array_key_exists('termId', $row) ) continue;
                $termId = $row['termId'];
                $termsConfig[$studentId][$termId]['outOf'] = $row['outOf'];
            }

            foreach( $studentPayload[$studentId]['body']['rows'] as $index => $row )
            {
                $subjectId = $row['subjectId'];
                $stackedTotal = 0; $stackedMax = 0;
                foreach( $row['cells'] as $termId => $exams )
                {
                    $total = 0; $stackMax = 0; $normalsCount = 0; $is_skipped = false;
                    foreach( $exams as $key => $data )
                    {
                        if( $data['type'] == "normal" )
                        {
                            $normalsCount = $normalsCount + 1;
                            $examId = $data['exam'];
                            $getMark = exam_marks::select('id', 'totalMarks as mark')->where('examId', $examId)->where('classId', $classId)->where('subjectId', $subjectId)->where('studentId', $studentId)->first();
                            if( $getMark )
                            {
                                $virtualMark = $getMark->mark;
                                if( !is_numeric( $getMark->mark ) ) { $mark = 0; }
                                elseif( $getMark->mark == "" ) { $mark = 0; }
                                else { $mark = floatval($getMark->mark); }
                                if( $getMark->mark != "" ) { if( !is_numeric( $getMark->mark ) ) { $is_skipped = true; } }
                            } else { $mark = 0; $virtualMark = 0; $is_skipped = true; }
                            if( !$is_skipped ) { $total = $total + $mark; }
                            $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['value'] = $virtualMark;
                            if( isset($loadedExams[$examId]['schedule'][$subjectId]) ) $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max'] = $loadedExams[$examId]['schedule'][$subjectId]['max'];
                            else $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max'] = 0;
                            if( $is_skipped )
                            {
                                $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max'] = $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max'] - $loadedExams[$examId]['schedule'][$subjectId]['max'];
                            }
                            if( !$is_skipped ) { $stackMax = $stackMax + $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max']; }
                            $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['pass'] = true;
                            if( isset( $payload['settings']['passMarks'] ) )
                            {
                                if( $payload['settings']['passMarks'] != 0 )
                                {
                                    $passMarks = $payload['settings']['passMarks'];
                                    $prcng = $stackMax == 0 ? 0 :round( ( ($total / $stackMax) * 100 ), 2);
                                    if( $prcng >= $passMarks ) {}
                                    else $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['pass'] = false;
                                }
                            }
                            $stackedTotal = $stackedTotal + $mark;
                            $stackedMax = $stackedMax + $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max'];
                        }
                        else
                        {
                            $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['value'] = $total;
                            $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max'] = $stackMax;
                            $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['pass'] = true;
                            if( isset( $payload['settings']['passMarks'] ) )
                            {
                                if( $payload['settings']['passMarks'] != 0 )
                                {
                                    $passMarks = $payload['settings']['passMarks'];
                                    $prcng = $stackMax == 0 ? 0 :round( ( ($total / $stackMax) * 100 ), 2);
                                    if( $prcng >= $passMarks ) {}
                                    else $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['pass'] = false;
                                }
                            }
                            if( $data['type'] == "averageCell" )
                            {
                                $avg = $normalsCount == 0 ? 0 : ( round(($total / $normalsCount) , 2) );
                                $maxAvg = $normalsCount == 0 ? 0 : ( round(($stackMax / $normalsCount) , 2) );
                                $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['value'] = $avg;
                                $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max'] = $maxAvg;

                            }
                            if( $data['type'] == "gradeCell" )
                            {
                                $prcng = $stackMax == 0 ? 0 :round( ( ($total / $stackMax) * 100 ), 2);
                                $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['nValue'] = $total;
                                $getGrade = $this->getGrade( $payload['settings']['grades'], $prcng );
                                $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['value'] = $getGrade;
                            }
                            if( isset( $termsConfig[$studentId][$termId]['outOf'] ) && $data['type'] != "gradeCell" )
                            {
                                if( $termsConfig[$studentId][$termId]['outOf'] != 0 )
                                {
                                    $outOf = $termsConfig[$studentId][$termId]['outOf'];
                                    $thisValue = $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['value'];
                                    $thisMax = $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max'];
                                    $thisOutOf = $thisMax == 0 ? $thisValue : ( round( ( ( $thisValue * $outOf) /$thisMax), 2) );
                                    $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['nValue'] = $thisValue;
                                    $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['value'] = $thisOutOf;
                                    if( $data['type'] == "averageCell" )
                                    {
                                        $modfy = $normalsCount == 0 ? 0 : round( ($thisOutOf / $normalsCount) , 2);
                                        $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['value'] = $modfy;
                                    }
                                }
                            }
                        }
                    }
                    if( isset( $payload['settings']['cumulative']['choosen'] ) )
                    {
                        if( count( $payload['settings']['cumulative'] ) > 0 )
                        {
                            if( array_key_exists('choosen', $payload['settings']['cumulative']) )
                            {
                                if( $payload['settings']['cumulative']['choosen'] == true )
                                {
                                    $studentPayload[$studentId]['body']['rows'][$index]['cells']["cumm"][0] = [
                                        "type" => "cummCell",
                                        "value" => $stackedTotal,
                                        "max" => $stackedMax,
                                        "pass" => true
                                    ];
                                    if( isset( $payload['settings']['passMarks'] ) )
                                    {
                                        if( $payload['settings']['passMarks'] != 0 )
                                        {
                                            $passMarks = $payload['settings']['passMarks'];
                                            $prcng = $stackedMax == 0 ? 0 :round( ( ($stackedTotal / $stackedMax) * 100 ), 2);
                                            if( $prcng >= $passMarks ) {}
                                            else $studentPayload[$studentId]['body']['rows'][$index]['cells']["cumm"][0]['pass'] = false;
                                        }
                                    }
        
                                    if( isset($payload['settings']['cumulative']['outOf']) )
                                    {
                                        if( $payload['settings']['cumulative']['outOf'] != 0 )
                                        {
                                            $stackOutOf = $payload['settings']['cumulative']['outOf'];
                                            $prcng = $stackedMax == 0 ? 0 :round( ( ($stackedTotal * $stackOutOf / $stackedMax) ), 2);
                                            $studentPayload[$studentId]['body']['rows'][$index]['cells']["cumm"][0]['nValue'] = $stackedTotal;
                                            $studentPayload[$studentId]['body']['rows'][$index]['cells']["cumm"][0]['value'] = $prcng;
                                        }
                                    }
                                    if( !isset($termsConfig[$studentId]["cumm"]) )
                                    {
                                        $termsConfig[$studentId]["cumm"] = [];
                                        $termsConfig[$studentId]["cumm"]["total"] = 0;
                                        $termsConfig[$studentId]["cumm"]["max"] = 0;
                                    }
                                    $termsConfig[$studentId]["cumm"]["total"] = $termsConfig[$studentId]["cumm"]["total"] + $total;
                                    $termsConfig[$studentId]["cumm"]["max"] = $termsConfig[$studentId]["cumm"]["max"] + $stackMax;
                                }
                            }
                        }
                    }
                }
            }

            foreach( $studentPayload[$studentId]['body']['rows'] as $index => $row )
            {
                foreach( $row['cells'] as $termId => $term )
                {
                    $stackMax = 0;
                    foreach($term as $key => $mark)
                    {
                        if($mark["type"] == "normal")
                        {
                            $markValue = $mark["value"];
                            if( !is_numeric($markValue) ) $markValue = 0;
                            $max = !isset( $mark["max"] ) ? 0 : $mark["max"];
                            $termsConfig[$studentId][$termId]['total'] = $termsConfig[$studentId][$termId]['total'] + $markValue;
                            if( is_numeric($markValue) ) { $termsConfig[$studentId][$termId]['max'] = $termsConfig[$studentId][$termId]['max'] + $max; }
                            $stackMax = $stackMax + $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['max'];
                        }
                    }
                }
            }

            foreach( $studentPayload[$studentId]['body']['rows'] as $index => $row )
            {
                foreach( $row['cells'] as $termId => $term )
                {
                    foreach($term as $key => $mark)
                    {
                        if($mark["type"] == "normal")
                        {
                            if( isset($mark["outOf"]) )
                            {
                                if( $mark["outOf"] != 0 )
                                {
                                    $markoutOf = $mark["outOf"];
                                    $max = isset($mark["max"]) ? $mark["max"] : 0;
                                    if( array_key_exists('nValue', $mark) )
                                    {
                                        if( is_numeric( $mark['nValue'] ) )
                                        {
                                            $localOutOf = $max == 0 ? 0 : ( round( ( ($markoutOf * $mark['nValue']) / $max ), 2) );
                                            $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['nValue'] = $mark['nValue'];
                                            $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['value'] = $localOutOf;
                                        }
                                    }
                                    elseif( is_numeric( $mark['value'] ) )
                                    {
                                        $localOutOf = $max == 0 ? 0 : ( round( ( ($markoutOf * $mark['value']) / $max ), 2) );
                                        $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['nValue'] = $mark['value'];
                                        $studentPayload[$studentId]['body']['rows'][$index]['cells'][$termId][$key]['value'] = $localOutOf;
                                    }
                                }
                            }
                        } else continue;
                    }
                }
            }
            foreach( $studentPayload[$studentId]['footer']['rows'] as $index => $oneRow )
            {
                foreach( $oneRow['cells'] as $key => $oneCell )
                {
                    $colspan = $oneCell['colspan']; $rowspan = $oneCell['rowspan'];
                    if( $oneCell['type'] == "total" )
                    {
                        $term = $oneCell['term'];
                        if( $term == 0 || $term == "cumm" ) $term = "cumm";
                        $val = $termsConfig[$studentId][$term]['total'];
                        $max = $termsConfig[$studentId][$term]['max'];
                        $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['value'] = $val;
                        $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['max'] = $max;
                        $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['pass'] = true;
                        if( isset( $termsConfig[$studentId][$term]['outOf'] ) )
                        {
                            if( $termsConfig[$studentId][$term]['outOf'] != 0 )
                            {
                                $outOfTotal = $termsConfig[$studentId][$term]['outOf'];
                                $scopedOutOf = $max == 0 ? 0 : ( round( (($val * $outOfTotal)/ $max), 2) );
                                $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['nValue'] = $val;
                                $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['value'] = $scopedOutOf;
                            }
                            if( isset( $payload['settings']['passMarks'] ) )
                            {
                                if( $payload['settings']['passMarks'] != 0 )
                                {
                                    $passMarks = $payload['settings']['passMarks'];
                                    $pcg = $max == 0 ? 0 : ( round( (($val / $max) * 100), 2) );
                                    if( $pcg >= $passMarks ) {}
                                    else $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['pass'] = false;
                                }
                            }
                        }
                    }
                    elseif( $oneCell['type'] == "percent" )
                    {
                        $term = $oneCell['term'];
                        if( $term == 0 || $term == "cumm" ) $term = "cumm";
                        $val = $termsConfig[$studentId][$term]['total'];
                        $max = $termsConfig[$studentId][$term]['max'];
                        if( $max == 0 ) $prg = 0;
                        else
                        {
                            $perecntge = ( $val/ $max ) * 100;
                            $prg = round( $perecntge, 2);
                        }
                        $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['value'] = $prg;
                        $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['max'] = $max;
                        $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['pass'] = true;
                        if( isset( $payload['settings']['passMarks'] ) )
                        {
                            if( $payload['settings']['passMarks'] != 0 )
                            {
                                $passMarks = $payload['settings']['passMarks'];
                                $pcg = $max == 0 ? 0 : ( round( (($val / $max) * 100), 2) );
                                if( $pcg >= $passMarks ) {}
                                else $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['pass'] = false;
                            }
                        }
                    }
                    elseif( $oneCell['type'] == "rank" )
                    {
                        $term = $oneCell['term'];
                        if( $term == 0 || $term == "cumm" ) $term = "cumm";
                        if( !isset($termsConfig[$studentId][$term]) ) $val = 0;
                        else $val = $termsConfig[$studentId][$term]['total'];
                        $studentRanks[$term][$studentId] = $val;
                        // $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['value'] = $studentRanks[$term][$studentId];
                    }
                    elseif( $oneCell['type'] == "editable" )
                    {
                        $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['value'] = "Promoted";
                    }
                }
            }
            $studentPayload[$studentId]['termsConfig'] = $termsConfig[$studentId];
            $studentPayload[$studentId]['settings'] = $payload['settings'];
        }
        foreach( $studentRanks as $termId => $student )
        {
            arsort($student);
            $studentRanks[$termId] = $student;
        }
        foreach( $studentRanks as $termId => $marks )
        {
            $position = 1;
            foreach( $marks as $studentId => $mark )
            {
                $pos = substr($position, -1);
                if( intval($mark) == 0 ) $studentRanks[$termId][$studentId] = "No Rank";
                else
                {
                    if( $pos == 1 ) $studentRanks[$termId][$studentId] = $position . "st";
                    elseif( $pos == 2 ) $studentRanks[$termId][$studentId] = $position . "nd";
                    elseif( $pos == 3 ) $studentRanks[$termId][$studentId] = $position . "rd";
                    else $studentRanks[$termId][$studentId] = $position . "th";
                }
                $position = $position + 1;
            }
        }

        foreach( $students as $studentId )
        {
            foreach( $studentPayload[$studentId]['footer']['rows'] as $index => $oneRow )
            {
                foreach( $oneRow['cells'] as $key => $oneCell )
                {
                    if( $oneCell['type'] == "rank" )
                    {
                        $term = $oneCell['term'];
                        if( $term == 0 || $term == "cumm" ) $term = "cumm";
                        // if( !isset($termsConfig[$term]) ) $val = 0;
                        // else $val = $termsConfig[$term]['total'];
                        // $studentRanks[$term][$studentId] = $val;
                        $studentPayload[$studentId]['footer']['rows'][$index]['cells'][$key]['value'] = $studentRanks[$term][$studentId];
                    }
                }
            }
        }
        foreach( $studentPayload as $studentId => $onePayload )
        {
            $studentSheet = MarkSheetStudents::where('class_id', $classId)->where('section_id', $sectionId)->where('student_id', $studentId)->where('marksheet_id', $sheetId)->first();
            if( !$studentSheet ) $studentSheet = new MarkSheetStudents();
            $studentSheet->file_name = NULL;
            $studentSheet->class_id = $classId;
            $studentSheet->section_id = $sectionId;
            $studentSheet->student_id = $studentId;
            $studentSheet->marksheet_id = $sheetId;
            $studentSheet->payload = json_encode( $onePayload );
            $studentSheet->rank = 0;
            $studentSheet->updated_by = 1;
            if( !$studentSheet ) $studentSheet->created_at = date('Y-m-d H:i:s');
            $studentSheet->updated_at = date('Y-m-d H:i:s');
            $studentSheet->save();
        }

        $generatedSheet = MarkSheet::find($sheetId);
        $generatedSheet->status = "completed";
        $generatedSheet->save();
    }

    function getGrade( $grades, $needle )
    {
        foreach( $grades as $gradeItem )
        {
            $gradeType = $gradeItem['type'];
            $role_1 = $gradeItem['role_1'];
            $role_2 = $gradeItem['role_2'];
            $grade = $gradeItem['grade'];
            if( $gradeType == "gt" ) { if( $needle > $role_1 ) { return $grade; } }
            if( $gradeType == "gte" ) { if( $needle >= $role_1 ) { return $grade; } }
            if( $gradeType == "et" ) { if( $needle == $role_1 ) { return $grade; } }
            if( $gradeType == "lte" ) { if( $needle <= $role_1 ) { return $grade; } }
            if( $gradeType == "lt" ) { if( $needle < $role_1 ) { return $grade; } }
            if( $gradeType == "bt" ) { if( $needle >= $role_1 && $needle <= $role_2 ) { return $grade; } }
        }
        return "";
    }

    public function failed( Exception $exception )
    {
        $sheetId = $this->currentSheetId;
        $generatedSheet = MarkSheet::find($sheetId);
        $generatedSheet->status = "failed";
        $generatedSheet->save();
        \Log::error($exception->getMessage());
    }
}