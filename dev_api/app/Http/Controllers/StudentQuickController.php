<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models2\User;
use App\Models2\MClass;
use App\Models2\Main;
use App\Models2\Section;
use App\Models2\Hostelry;
use App\Models2\Transportation;
use App\Models2\transport_vehicles;
use App\Models2\StudentType;
use App\Models2\StudentCategory;
use App\Models2\roles;
use App\Models2\payments;
use App\Models2\student_academic_years;

class StudentQuickController extends Controller
{
    var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

    public function __construct()
    {
		if(app('request')->header('Authorization') != "" || \Input::has('token')) $this->middleware('jwt.auth');
		else $this->middleware('authApplication');

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['users'] = $this->panelInit->getAuthUser();
        if(!isset($this->data['users']->id)) return \Redirect::to('/');
        User::$withoutAppends = true;
    }

    public function load_data()
    {
        $toReturn = array();
        $toReturn['classes'] = MClass::select('id', 'className as name')->get()->toArray();
        $toReturn['sections'] = Section::getSectionsUponClass();
        $toReturn['stoppage'] = [];
        $toReturn['vehicles'] = [];
        $toReturn['categories'] = StudentCategory::select('id', 'cat_title as name')->get()->toArray();
        $toReturn['hostels'] = [];
        $toReturn['types'] = StudentType::select('id', 'title as name')->get()->toArray();
        return $toReturn;
    }

    public function sibling_search()
    {
        if( !\Input::has('searchText') )
        {
            return $this->panelInit->apiOutput(false, 'Search for Siblings', 'Nothing to search for' ); 
        }
        $searchText = \Input::get('searchText');
        if( strlen( $searchText ) < 3 ) return $this->panelInit->apiOutput(false, 'Search for Siblings', 'Min character length is 3' );
        $students = User::where('role','student')->where(
            function($query) use ($searchText)
            {
                $query
                    ->where('fullName','like','%'.$searchText.'%')
                    ->orWhere('username','like','%'.$searchText.'%')
                    ->orWhere('email','like','%'.$searchText.'%');
            }
        )->get();
        $toReturn = array();
        $toReturn['status'] = 'success';
        $toReturn['siblings'] = array();
        foreach ($students as $index => $student)
        {
            $studentId = $student->id;
            if( \Input::has('studentId') )
            {
                if( intval( $studentId ) == intval( \Input::get('studentId') ) ) { unset( $students[$index] ); continue; }
            }
            $guardian = User::where('parentOf', 'like', '%"id":' . $studentId . '%')->first();
            if( !$guardian ) continue;
            $parentOf = json_decode( $guardian->parentOf, true );
            if( json_last_error() != JSON_ERROR_NONE ) continue;
            if( !count( $parentOf ) ) continue;

            $father = json_decode($student->father_info, true);
            if( json_last_error() != JSON_ERROR_NONE ) $father = ["name" => "", "phone" => "", "job" => "", "notes" => "", "qualification" => "", "email" => "", "address" => ""];
            $mother = json_decode($student->mother_info, true);
            if( json_last_error() != JSON_ERROR_NONE ) $mother = ["name" => "", "phone" => "", "job" => "", "notes" => "", "qualification" => "", "email" => "", "address" => ""];
            
            $guardianDetails = $parentOf[0];
            $toReturn['siblings'][ $guardian->id ] = [
                'id' => $student->id,
                'relation' => $guardianDetails['relation'],
                'studentName' => $student->fullName,
                'studentAdmission' => $student->admission_number,
                'gaurdianId' => $guardian->id,
                'gaurdianName' => $guardian->fullName,
                'mail' => $guardian->email,
                'mobileNo' => trim( $guardian->phoneNo ) ? $guardian->phoneNo : ( trim( $guardian->mobileNo ) ? $guardian->mobileNo : '' ),
                'username' => $guardian->username,
                'father' => [
                    'name' => array_key_exists( 'name', $father ) ? $father['name'] : '',
                    'mobile' =>  array_key_exists( 'phone', $father ) ? $father['phone'] : '',
                    'job' =>  array_key_exists( 'job', $father ) ? $father['job'] : '',
                    'qualification' =>  array_key_exists( 'qualification', $father ) ? $father['qualification'] : '',
                    'email' =>  array_key_exists( 'email', $father ) ? $father['email'] : '',
                    'address' =>  array_key_exists( 'address', $father ) ? $father['address'] : ''
                ],
                'mother' => [
                    'name' => array_key_exists( 'name', $mother ) ? $mother['name'] : '',
                    'mobile' =>  array_key_exists( 'phone', $mother ) ? $mother['phone'] : '',
                    'job' =>  array_key_exists( 'job', $mother ) ? $mother['job'] : '',
                    'qualification' =>  array_key_exists( 'qualification', $mother ) ? $mother['qualification'] : '',
                    'email' =>  array_key_exists( 'email', $mother ) ? $mother['email'] : '',
                    'address' =>  array_key_exists( 'address', $mother ) ? $mother['address'] : ''
                ],
                'gaurdian' => []
            ];
            $toReturn['siblings'][ $guardian->id ]['gaurdian'] = [
                "id" => $guardian->id,
                "name" => $guardian->fullName,
                "relation" => array_key_exists("relation", $guardianDetails) ? $guardianDetails["relation"] : "",
                "mobile" => array_key_exists("phone", $guardianDetails) ? $guardianDetails["phone"] : ( trim( $guardian->mobileNo ) ? $guardian->mobileNo : "" ),
                "email" => $guardian->email,
                "username" => array_key_exists("username", $guardianDetails) ? $guardianDetails["username"] : $guardian->username,
                "job" => array_key_exists("job", $guardianDetails) ? $guardianDetails["job"] : "",
                "address" =>  array_key_exists("address", $guardianDetails) ? $guardianDetails["address"] : $guardian->address
            ];
        }
        $toReturn['siblingsCount'] = count( $toReturn['siblings'] );
        return $toReturn;
    }

    public function register()
    {
        $comVia = [];
        User::$withoutAppends = true;
        if( \Input::has('mail') ) { if( \Input::get('mail') ) $comVia[] = "mail"; }
        if( \Input::has('sms') ) { if( \Input::get('sms') ) $comVia[] = "sms"; }
        if( \Input::has('phone') ) { if( \Input::get('phone') ) $comVia[] = "phone"; }
        if( !\Input::has('admissionNo') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Student admission number is missing"); }
        else
        {
            if( User::where('admission_number', \Input::get('admissionNo') )->count() > 0 ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Student Admission number already used before."); }
        }
        if( !\Input::has('firstName') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Student first name is missing"); }
        if( !\Input::has('dateOfBirth') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Student date of birth is missing"); }
        if( !\Input::has('classId') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Student class is missing"); }
        if( !\Input::has('sectionId') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Student section is missing"); }

        if( !\Input::has('guardianType') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Guardian type is missing"); }
        if( !\Input::has('guardianName') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Guardian name is missing"); }
        if( !\Input::has('guardianRelation') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Guardian relation is missing"); }
        if( !\Input::has('guardianPhone') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Guardian phone is missing"); }
        if( !\Input::has('guardianAddress') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Guardian address is missing"); }
        if( !\Input::has('guardianUsername') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Guardian username is missing"); }
        if( !\Input::has('guardianPassword') ) { return $this->panelInit->apiOutput( false, $this->panelInit->language['addStudent'], "Guardian password is missing"); }

        if( \Input::hasFile('userPhoto') )
        {
            $fileInstance = \Input::file('userPhoto');
            if( !$this->panelInit->validate_upload( $fileInstance ) )
            {
				return $this->panelInit->apiOutput(false,$this->panelInit->language['addStudent'],"Sorry, This File Type Is Not Permitted For Security Reasons");
			}
        }
        if( \Input::get('guardianId') == "false" )
        {
            // Get the default role
            $def_role = roles::where('def_for','parent')->select('id');
            if( $def_role->count() == 0 )
            {
                return $this->panelInit->apiOutput(false, $this->panelInit->language['addStudent'], 'No default role assigned for parents, Please contact administartor');
            }
            $def_role = $def_role->first();
            if( User::where('username',  \Input::get('guardianUsername') )->count() > 0){
                return $this->panelInit->apiOutput(false, $this->panelInit->language['addStudent'] , $this->panelInit->language['usernameUsed']);
            }
            $type = \Input::get('guardianType');

            if( trim( \Input::get('gaurdianMail') ) )
            {
                $matchMail = \Input::get('gaurdianMail');
            } else  $matchMail = $type == 'mother' ? \Input::get('motherEmail') : \Input::get('fatherEmail');
            if( trim( $matchMail ) || trim( $matchMail ) != "" )
            {
                if( User::where('email', $matchMail )->count() > 0)
                {
                    return $this->panelInit->apiOutput(false, $this->panelInit->language['addStudent'] , $this->panelInit->language['mailUsed']);
                }
            }
            
            $parantUser = new User();
		    $parantUser->username = \Input::get('guardianUsername');
            $parantUser->email = $matchMail;
            $parantUser->fullName = \Input::get('guardianName');
            $parantUser->password = \Hash::make( \Input::get('guardianPassword') );
		    $parantUser->role = "parent";
            $parantUser->mobileNo = \Input::get('guardianPhone');
            $parantUser->address = \Input::get('guardianAddress');
            $parantUser->comVia = json_encode( $comVia );
            $parantUser->account_active = "1";
            $parantUser->role_perm = $def_role->id;
            $parantUser->save();
            $parentId = $parantUser->id;
            user_log('Parents', 'create', $parantUser->fullName);
        } else { $parentId = \Input::get('guardianId'); }
        
        // Get the default role
		$def_student_role = roles::where('def_for','student')->select('id');
        if( $def_student_role->count() == 0 )
        {
			return $this->panelInit->apiOutput(false, $this->panelInit->language['addStudent'], 'No default role assigned for student, Please contact administartor');
        }
        $def_student_role = $def_student_role->first();
        $fullName = trim( \Input::get('firstName') );
        if( \Input::has('middleName') ) $fullName .= " " . trim( \Input::get('middleName') );
        if( \Input::has('lastName') ) $fullName .= " " . trim( \Input::get('lastName') );

        $Student = new User();
        $Student->username = \Input::get('admissionNo');
        $Student->password = \Hash::make( \Input::get('admissionNo') . '123' );
        $Student->fullName = $fullName;
        $Student->role = "student";
        $Student->studentAcademicYear = $this->panelInit->selectAcYear;
        $Student->studentClass = \Input::get('classId');
        $Student->studentSection = \Input::get('sectionId');
        $Student->comVia = json_encode( $comVia );
        $Student->admission_number = \Input::get('admissionNo');
        if( \Input::has('admissionDate') ) { $Student->admission_date = toUnixStamp( \Input::get('admissionDate') ); }
        if( \Input::has('dateOfBirth') ) { $Student->birthday = toUnixStamp( \Input::get('dateOfBirth') ); }
        if( \Input::has('gender') ) { $Student->gender = \Input::get('gender'); }
        if( \Input::has('birthPlace') ) { $Student->birthPlace = \Input::get('birthPlace'); }
        if( \Input::has('nationality') ) { $Student->nationality = \Input::get('nationality'); }
        if( \Input::has('religion') ) { $Student->religion = \Input::get('religion'); }
        if( \Input::has('studentCategory') ) { $Student->std_category = \Input::get('studentCategory'); }
        if( \Input::has('studentType') ) { $Student->studentType = \Input::get('studentType'); }
        if( \Input::has('rollNo') ) { $Student->studentRollId = \Input::get('rollNo'); }
        if( \Input::has('bioId') )
        {
            $Student->bioId = \Input::get('bioId');
            $Student->biometric_id = \Input::get('bioId');
        }
        if( \Input::has('corresAddressLine') ) { $Student->address = \Input::get('corresAddressLine'); }
        elseif( \Input::has('permaAddressLine') ) { $Student->address = \Input::get('permaAddressLine'); }

        if( \Input::has('corresAddressPhone') ) { $Student->phoneNo = \Input::get('corresAddressPhone'); }
        elseif( \Input::has('permaAddressPhone') ) { $Student->phoneNo = \Input::get('permaAddressPhone'); }

        if( \Input::has('corresAddressMobile') ) { $Student->mobileNo = \Input::get('corresAddressMobile'); }
        elseif( \Input::has('permaAddressMobile') ) { $Student->mobileNo = \Input::get('permaAddressMobile'); }
        
        if( \Input::has('stoppage') ) { $Student->transport = \Input::get('stoppage'); }
        if( \Input::has('transport') ) { $Student->transport_vehicle = \Input::get('transport'); }
        if( \Input::has('hostel') ) { $Student->hostel = \Input::get('hostel'); }
        if( \Input::has('room') ) { $Student->room = \Input::get('room'); }
        
        $corres_address = []; $perma_address = []; $medical = [];
        
        $corres_address['line'] = \Input::has('corresAddressLine') ? \Input::get('corresAddressLine') : "";
        $corres_address['city'] = \Input::has('corresAddressCity') ? \Input::get('corresAddressCity') : "";
        $corres_address['state'] = \Input::has('corresAddressState') ? \Input::get('corresAddressState') : "";
        $corres_address['pin'] = \Input::has('corresAddressPin') ? \Input::get('corresAddressPin') : "";
        $corres_address['country'] = \Input::has('corresAddressCountry') ? \Input::get('corresAddressCountry') : "";
        $corres_address['phone'] = \Input::has('corresAddressPhone') ? \Input::get('corresAddressPhone') : "";
        $corres_address['Mobile'] = \Input::has('corresAddressMobile') ? \Input::get('corresAddressMobile') : "";

        $perma_address['line']  = \Input::has('permaAddressLine') ? \Input::get('permaAddressLine') : "";
        $perma_address['city']  = \Input::has('permaAddressCity') ? \Input::get('permaAddressCity') : "";
        $perma_address['state']  = \Input::has('permaAddressState') ? \Input::get('permaAddressState') : "";
        $perma_address['pin']  = \Input::has('permaAddressPin') ? \Input::get('permaAddressPin') : "";
        $perma_address['country']  = \Input::has('permaAddressCountry') ? \Input::get('permaAddressCountry') : "";
        $perma_address['phone']  = \Input::has('permaAddressPhone') ? \Input::get('permaAddressPhone') : "";
        $perma_address['Mobile']  = \Input::has('permaAddressMobile') ? \Input::get('permaAddressMobile') : "";

        $medical['inspol'] = \Input::has('inspol') ? \Input::get('inspol') : "";
        $medical['blood_group'] = \Input::has('bloodType') ? \Input::get('bloodType') : "";
        $medical['weight'] = \Input::has('weight') ? \Input::get('weight') : "";
        $medical['height'] = \Input::has('height') ? \Input::get('height') : "";
        $medical['disab'] = \Input::has('disab') ? \Input::get('disab') : "";
        $medical['contact'] = \Input::has('contact') ? \Input::get('contact') : "";
        
        $Student->corres_address = json_encode( $corres_address );
        $Student->perma_address = json_encode( $perma_address );
        $Student->medical = json_encode( $medical );

        $previous = [];

        $previous['institution'] = \Input::has('previousInstitution') ? \Input::get('previousInstitution') : "";
        $previous['class'] = \Input::has('previousClass') ? \Input::get('previousClass') : "";
        $previous['year'] = \Input::has('previousYear') ? \Input::get('previousYear') : "";
        $previous['percentage'] = \Input::has('previousPercentage') ? \Input::get('previousPercentage') : "";
        $previous_data[] = $previous;
        if(
            \Input::has('previousInstitution') || \Input::has('previousClass') ||
            \Input::has('previousYear') || \Input::has('previousPercentage')
        ) { $Student->previous_data = json_encode( $previous_data ); }

        $Student->isLeaderBoard = "";
        $Student->account_active = "1";
        $Student->role_perm = $def_student_role->id;
        $Student->save();
        
        $studentId = $Student->id;
        $currentStudent = User::find( $studentId );
        if( \Input::hasFile('userPhoto') )
        {
			$fileInstance = \Input::file('userPhoto');
            if( !$this->panelInit->validate_upload($fileInstance) )
            {
				return $this->panelInit->apiOutput(false,$this->panelInit->language['addStudent'],"Sorry, This File Type Is Not Permitted For Security Reasons");
			}
			$newStudentPhotoName = "profile_" . $Student->id . ".jpg";
			$file = $fileInstance->move(uploads_config()['uploads_file_path'] . '/profile/', $newStudentPhotoName);
			$currentStudent->photo = "profile_" . $studentId . ".jpg";
        }

        if( \Input::get('guardianId') == "false" ) { $allWards = []; }
        else
        {
            $allWards = [];
            $sibilingsIds = [];
            $parent = User::find( $parentId );
            $oldSibilings = json_decode( $parent->parentOf, true );
            if( json_last_error() != JSON_ERROR_NONE ) $oldSibilings = [];
            foreach( $oldSibilings as $oneSibiling ) { $sibilingsIds[] = $oneSibiling['id']; }
            $allWards = User::select('id', 'fullNamr', 'father_info', 'mother_info')->whereIn('id', $sibilingsIds)->get()->toArray();
        }
        
        $father_info['name'] = \Input::has('fatherName') ? \Input::get('fatherName') : "";
        $father_info['phone'] = \Input::has('fatherPhone') ? \Input::get('fatherPhone') : "";
        $father_info['job'] = \Input::has('fatherJob') ? \Input::get('fatherJob') : "";
        $father_info['qualification'] = \Input::has('fatherQualification') ? \Input::get('fatherQualification') : "";
        $father_info['email'] = \Input::has('fatherEmail') ? \Input::get('fatherEmail') : "";
        $father_info['address'] = \Input::has('fatherAddress') ? \Input::get('fatherAddress') : $corres_address['line'];
        if( \Input::hasFile('fatherPhoto') )
        {
            $fatherPhotoInstance = \Input::file('fatherPhoto');
            if( $this->panelInit->validate_upload( $fatherPhotoInstance ) )
            {
                $newFatherFileName = "father_" . $studentId . ".jpg";
			    $fatherPhotoInstance->move(uploads_config()['uploads_file_path'] . '/parents/', $newFatherFileName);
                $father_info['pic'] = "father_" . $studentId . ".jpg";
            }
        }
        else
        {
            if( \Input::get('guardianId') == "false" ) { $father_info['pic'] = ""; }
            else
            {
                foreach( $allWards as $oneWard )
                {
                    $wardFather = json_decode( $oneWard['father_info'], true );
                    if( json_last_error() != JSON_ERROR_NONE ) $wardFather = [];
                    if( !array_key_exists('pic', $wardFather) ) { continue; }
                    else
                    {
                        $father_info['pic'] = $wardFather['pic'];
                    }
                }
                if( !array_key_exists('pic', $father_info) ) { $father_info['pic'] = ""; }
            }
        }
        
        $mother_info['name'] = \Input::has('motherName') ? \Input::get('motherName') : "";
        $mother_info['phone'] = \Input::has('motherPhone') ? \Input::get('motherPhone') : "";
        $mother_info['job'] = \Input::has('motherJob') ? \Input::get('motherJob') : "";
        $mother_info['qualification'] = \Input::has('motherQualification') ? \Input::get('motherQualification') : "";
        $mother_info['email'] = \Input::has('motherEmail') ? \Input::get('motherEmail') : "";
        $mother_info['address'] = \Input::has('motherAddress') ? \Input::get('motherAddress') : $corres_address['line'];
        if( \Input::hasFile('motherPhoto') )
        {
            $motherPhotoInstance = \Input::file('motherPhoto');
            if( $this->panelInit->validate_upload( $motherPhotoInstance ) )
            {
                $newMotherFileName = "mother_" . $studentId . ".jpg";
			    $motherPhotoInstance->move(uploads_config()['uploads_file_path'] . '/parents/', $newMotherFileName);
                $mother_info['pic'] = "mother_" . $studentId . ".jpg";
            }
        }
        else
        {
            if( \Input::get('guardianId') == "false" ) { $mother_info['pic'] = ""; }
            else
            {
                foreach( $allWards as $oneWard )
                {
                    $wardMother = json_decode( $oneWard['mother_info'], true );
                    if( json_last_error() != JSON_ERROR_NONE ) $wardMother = [];
                    if( !array_key_exists('pic', $wardMother) ) { continue; }
                    else
                    {
                        $mother_info['pic'] = $wardMother['pic'];
                    }
                }
                if( !array_key_exists('pic', $mother_info) ) { $mother_info['pic'] = ""; }
            }
        }
        
        $currentStudent->father_info = json_encode( $father_info );
        $currentStudent->mother_info = json_encode( $mother_info );
        
        $currentStudent->save();
        user_log('Students', 'create', $Student->fullName);
        
        $siblingData = [
            "id" => $studentId,
            "student" => $Student->fullName,
            "relation" => \Input::get('guardianRelation'),
            "job" => \Input::get('gaurdianJob'),
            "phone" => \Input::get('guardianPhone'),
            "address" => \Input::get('guardianAddress'),
            "username" => \Input::get('guardianUsername')
        ];
        $parentOf[] = $siblingData;

        $getParent = User::find( $parentId );
        $oldSibilings = json_decode( $getParent->parentOf, true );
        if( json_last_error() != JSON_ERROR_NONE ) $oldSibilings = [];
        $oldSibilings[] = $siblingData;
        $getParent->parentOf = json_encode( $oldSibilings );
        $getParent->save();

        if( \Input::has('filesCount') )
        {
            $filesCount = \Input::get('filesCount');
            if( $filesCount != 0 )
            {
                for( $i = 0; $i < $filesCount; $i++ )
                {
                    $docFile = "filesCount_" . $i . "_file";
                    $docTitle = "filesCount_" . $i . "_title";
                    $docNotes = "filesCount_" . $i . "_notes";
                    if( \Input::hasFile( $docFile ) )
                    {
                        $docFileInstance = \Input::file( $docFile );
                        if( !$this->panelInit->validate_upload($docFileInstance) ) continue;
                        else
                        {
                            $newFileName = $Student->id . "_" . uniqid() . "." . $docFileInstance->getClientOriginalExtension();
                            $file = $docFileInstance->move(uploads_config()['uploads_file_path'] . '/student_docs/', $newFileName);
                            $student_docs = new \student_docs();
                            $student_docs->user_id = $Student->id;
                            if( \Input::has( $docTitle ) ) { $student_docs->file_title = \Input::get( $docTitle ); }
                            $student_docs->file_name = $newFileName;
                            if( \Input::has( $docNotes ) ) { $student_docs->file_notes = \Input::get( $docNotes ); }
                            $student_docs->save();
                        }
                    } else continue;
                }
            }
        }
        
        // auto invoicing
            if( \Input::has('stoppage') )
            {
                $transportation = transportation::find( \Input::get('stoppage') );
                if( $transportation )
                {
                    for( $i= 1 ; $i <= 12 ; $i++ )
                    {
                        $start = $this->panelInit->settingsArray['invoice_sc_'.$i];
                        $due = $this->panelInit->settingsArray['invoice_sc_due_'.$i];
                        $xx = $this->panelInit->settingsArray['months_'.$i];
                        $fine_tra = $this->panelInit->settingsArray['fine_tra'];
                        if( !empty($start) && !empty($due) && !empty($xx) && strlen($start) > 6 && strlen($due) > 6 && $xx > 0 )
                        {
                            $payments = new payments();
                            $payments->paymentTitle = "Transport Fee";
                            $payments->paymentDescription = $transportation->transportTitle." fee of ".$xx." months";
                            $payments->paymentStudent = $Student->id;
                            $paymentRows = array();
                            $payments->paymentRows = json_encode($paymentRows);
                            $payments->paymentAmount = $transportation->transportFare * $xx;
                            $payments->paymentDiscounted = $transportation->transportFare * $xx;
                            $payments->paymentDate = toUnixStamp($start);
                            $payments->dueDate = toUnixStamp($due);
                            $payments->paymentUniqid = uniqid();
                            $payments->paymentStatus = "0";
                            $payments->fine_amount = $fine_tra;
                            $payments->save();
                        }
                    }
                }
            }

            $studentData = [];
            $studentData['type_id'] = \Input::has('studentType') ? \Input::get('studentType') : 0;
            $studentData['class_id'] = \Input::get('classId');
            $studentData['section_id'] = \Input::get('sectionId');

            // Get payment students ----------------------------------------------
                $students = User::where('role','student')->where('activated','1');
                if($studentData['class_id'] > 0) { $students = $students->where('studentClass', $studentData['class_id']); }
				if($studentData['section_id'] > 0) { $students = $students->where('studentSection', $studentData['section_id']); }
                if($studentData['type_id'] > 0) { $students = $students->where('studentType', $studentData['type_id']); }
                $paymentStudent = $students->distinct()->pluck('id')->toArray();
            // end Get payment students -------------------------------------------
            if( count( $paymentStudent ) )
            {
                $bulk_fees = payments::whereIn('paymentStudent', $paymentStudent)
                    ->where('paymentRows', '!=', '[]')
					->groupBy('paymentTitle', 'paymentDate', 'dueDate')
					->select('paymentTitle', 'paymentAmount', 'paymentRows', 'paymentDate', 'dueDate', 'fine_amount')
					->get()
                    ->toArray();
                
                if( count( $bulk_fees ) )
                {
                    foreach( $bulk_fees as $fee )
                    {
                        $payment = new payments;
                        $payment->paymentTitle = $fee['paymentTitle'];
						$payment->paymentStudent = $Student->id;
						$payment->paymentRows = $fee['paymentRows'];
						$payment->paymentAmount = $fee['paymentAmount'];
						$payment->paymentDiscounted = $fee['paymentAmount'];
						$payment->paymentDate = $fee['paymentDate'];
						$payment->dueDate = $fee['dueDate'];
						$payment->paymentUniqid = uniqid();
						$payment->paymentStatus = 0;
						$payment->fine_amount = $fee['fine_amount'];
						$payment->save();
                    }
                }
            }
        // **********************

        $studentAcademicYears = new student_academic_years();
		$studentAcademicYears->studentId = $Student->id;
		$studentAcademicYears->academicYearId = $this->panelInit->selectAcYear;
		$studentAcademicYears->classId = \Input::get('classId');
		$studentAcademicYears->sectionId = \Input::get('sectionId');
        $studentAcademicYears->save();
        return $this->panelInit->apiOutput(true, $this->panelInit->language['addStudent'], $this->panelInit->language['studentCreatedSuccess']);
    }

    public function excel_import()
    {
        if( \Input::hasFile('excelcsv') )
        {
            $classArray = array();
            $classArray_id = array();
            $stoppageArray = array();
            $ViecleArray = array();
            $HostelArray = array();
            $TypesArray = array();
            $CategoriesArray = array();
            $classes = \classes::where('classAcademicYear', $this->panelInit->selectAcYear)->get();
            $stoppages = Transportation::select('*')->get();
            $viecles = transport_vehicles::select('*')->get();
            $categories = StudentCategory::select('*')->get();
            $student_types = StudentType::select('*')->get();
            $hostels = Hostelry::select('*')->get();
            foreach( $stoppages as $stoppage ) { $stoppageArray[$stoppage->transportTitle] = $stoppage->id; }
            foreach( $viecles as $viecle )
            {
                $plate = $viecle->plate_number;
                $driver = $viecle->driver_name;
                $viecleKey = "$plate ($driver)";
                $ViecleArray[$viecleKey] = $viecle->id;
            }
            foreach( $hostels as $hostel ) { $HostelArray[$hostel->hostelTitle] = $hostel->id; }
            foreach( $categories as $category ) { $CategoriesArray[$category->cat_title] = $category->id; }
            foreach( $student_types as $student_type ) { $TypesArray[$student_type->title] = $student_type->id; }
            foreach( $classes as $class )
            {
                $classArray[$class->className] = $class->id;
				$classArray_id[$class->id] = [ 'id' => $class->id, 'className' => $class->className ];
            }
            $sectionsArray = array();
            $sections = \sections::get();
            foreach( $sections as $section )
            {
				if( isset( $classArray_id[$section->classId]) ) { $sectionsArray[$section->classId][$section->id] = $section->sectionName; }
            }
            if ( $_FILES['excelcsv']['tmp_name'] )
            {
				user_log('Students', 'import');
				$readExcel = \Excel::load( $_FILES['excelcsv']['tmp_name'], function($reader) { } )->get();
				$dataImport = array( "ready" => array(), "revise" => array() );
                foreach( $readExcel as $cell )
				{
                    $importItem = array();
                    // foreach( $row as $cell )
                    // {
                        if( isset( $cell->admission_no ) && $cell->admission_no != null )
                        {
                            $importItem['admissionNo'] = $cell->admission_no;
                        } else $importItem['error'][] = "admission no missing";
    
                        if( isset( $cell->first_name ) && $cell->first_name != null )
                        {
                            $importItem['firstName'] = $cell->first_name;
                            $fName = $cell->first_name;
                        }
                        else
                        {
                            $fName = "";
                            $importItem['error'][] = "first name missing";
                        }
    
                        if( isset( $cell->date_of_birth ) && $cell->date_of_birth != null )
                        {
                            if( !toUnixStamp( $cell->date_of_birth ) ) { $importItem['error'][] = "invalid birthday format"; }
                            else
                            {
                                $importItem['dateOfBirth'] = date('Y/m/d', toUnixStamp($cell->date_of_birth));
                            }
                        } else $importItem['error'][] = "birthday missing";
    
                        if( isset( $cell->sibling ) && $cell->sibling != null )
                        {
                            $studentAdmission = $cell->sibling;
                            if( User::where('admission_number', $studentAdmission )->count() > 0 ) { $importItem['error'][] = "invalid sibling admission"; }
                            else { $importItem['guardianId'] = $cell->sibling; }
                        }
                        else
                        {
                            $importItem['guardianId'] = NULL;
                            if( isset( $cell->guardian_username) && $cell->guardian_username != null )
                            {
                                $guardianUsername = $cell->guardian_username;
                                if( User::where('username', $guardianUsername )->count() > 0 ) { $importItem['error'][] = "guardian username already exsists"; }
                                else { $importItem['guardianUsername'] = $cell->guardian_username; }
                            } else $importItem['error'][] = "guardian username missing";
    
                            if( isset( $cell->guardian_relation) && $cell->guardian_relation != null )
                            {
                                $importItem['guardianRelation'] = $cell->guardian_relation;
                                if( $cell->guardian_relation == 'mother' )
                                {
                                    if( isset( $cell->mother_email) && $cell->mother_email != null )
                                    {
                                        $guardianEmail = $cell->mother_email;
                                        if( User::where('email', $guardianEmail )->count() > 0 )
                                        {
                                            $importItem['error'][] = "guardian email already exsists";
                                        } else { $importItem['guardianEmail'] = $cell->mother_email; }
                                    } // else $importItem['error'][] = "guardian email missing";
                                }
                                else
                                {
                                    if( isset( $cell->father_email) && $cell->father_email != null )
                                    {
                                        $guardianEmail = $cell->father_email;
                                        if( User::where('email', $guardianEmail )->count() > 0 )
                                        {
                                            // continue;
                                            $importItem['error'][] = "guardian email already exsists";
                                        } else { $importItem['guardianEmail'] = $cell->father_email; }
                                    } // else $importItem['error'][] = "guardian email missing";
                                }
                            } else $importItem['error'][] = "guardian relation missing";
    
                            if( isset( $cell->guardian_name) && $cell->guardian_name != null )
                            {
                                $importItem['guardianName'] = $cell->guardian_name;
                            } else $importItem['error'][] = "guardian name missing";
    
                            if( isset( $cell->guardian_mobile) && $cell->guardian_mobile != null )
                            {
                                if( User::where('mobileNo', $cell->guardian_mobile )->count() > 0 )
                                {
                                    $importItem['error'][] = "guardian phone already exsists";
                                } else { $importItem['guardianPhone'] = $cell->guardian_mobile; }
                            } else $importItem['error'][] = "guardian phone missing";
    
                            if( isset( $cell->guardian_address) && $cell->guardian_address != null )
                            {
                                $importItem['guardianAddress'] = $cell->guardian_address;
                            } else $importItem['error'][] = "guardian address missing";
    
                            if( isset( $cell->guardian_password) && $cell->guardian_password != null )
                            {
                                $importItem['guardianPassword'] = $cell->guardian_password;
                            } else $importItem['error'][] = "guardian password missing";
                        }
                        
                        if( isset( $cell->class) && $cell->class != null )
                        {
                            $importItem['class'] = $cell->class;
                            $importItem['studentClass'] = ( isset( $classArray [ $cell->class ] ) ) ? $classArray[ $cell->class ] : '';
                            if($importItem['studentClass'] == "") { $importItem['error'][] = "class"; }
                        } else { $importItem['error'][] = "class"; }
                        
                        if( $this->panelInit->settingsArray['enableSections'] == true && isset( $cell->section ) && $cell->section != null )
                        {
                            $importItem['section'] = $cell->section;
                            if( $importItem['studentClass'] != '' )
                            {
                                $sectionDb = \sections::where('classId',$importItem['studentClass'])->where('sectionName',$cell->section)->select('id');
                                if($sectionDb->count() > 0)
                                {
                                    $sectionDb = $sectionDb->first();
                                    $importItem['studentSection'] = $sectionDb->id;
                                } else { $importItem['studentSection'] = ''; }
                            } else { $importItem['studentSection'] = ''; }
                        }
                        if( $this->panelInit->settingsArray['enableSections'] == true && ( !isset($importItem['studentSection']) || $importItem['studentSection'] == "" ) )
                        { $importItem['error'][] = "section"; }
                        
                        $middleName = isset( $cell->middle_name ) && $cell->middle_name != null ? $cell->middle_name : "";
                        $lastName = isset( $cell->last_name ) && $cell->last_name != null ? $cell->last_name : "";
                        $fullName = $fName . " " . $middleName . " " . $lastName;
                        $fullName = trim( $fullName );
                        $importItem['admissionDate'] = isset( $cell->admission_date ) && $cell->admission_date != null ? $cell->admission_date : "";
                        $importItem['middleName'] = $middleName;
                        $importItem['lastName'] = $lastName;
                        $importItem['fullName'] = $fullName;
                        $importItem['gender'] = isset( $cell->gender ) && $cell->gender != null ? $cell->gender : "";
                        $importItem['birthPlace'] = isset( $cell->birth_place ) && $cell->birth_place != null ? $cell->birth_place : "";
                        $importItem['nationality'] = isset( $cell->nationality ) && $cell->nationality != null ? $cell->nationality : "";
                        $importItem['religion'] = isset( $cell->religion ) && $cell->religion != null ? $cell->religion : "";
                        $importItem['studentCategory'] = isset( $cell->student_category ) && $cell->student_category != null ? $cell->student_category : "";
                        $importItem['studentType'] = isset( $cell->student_type ) && $cell->student_type != null ? $cell->student_type : "";
                        $importItem['rollNo'] = isset( $cell->roll_number ) && $cell->roll_number != null ? $cell->roll_number : "";
                        $importItem['bioId'] = isset( $cell->biometric_id ) && $cell->biometric_id != null ? $cell->biometric_id : "";
    
                        $importItem['previousInstitution'] = isset( $cell->previous_institution ) && $cell->previous_institution != null ? $cell->previous_institution : "";
                        $importItem['previousClass'] = isset( $cell->previous_class ) && $cell->previous_class != null ? $cell->previous_class : "";
                        $importItem['previousYear'] = isset( $cell->previous_year ) && $cell->previous_year != null ? $cell->previous_year : "";
                        $importItem['previousPercentage'] = isset( $cell->previous_percentage ) && $cell->previous_percentage != null ? $cell->previous_percentage : "";
                        $importItem['stoppage'] = isset( $cell->stoppage ) && $cell->stoppage != null ? $cell->stoppage : "";
                        $importItem['transport'] = isset( $cell->transport_vehicle ) && $cell->transport_vehicle != null ? $cell->transport_vehicle : "";
                        $importItem['hostel'] = isset( $cell->hostel ) && $cell->hostel != null ? $cell->hostel : "";

                        if( isset( $cell->stoppage ) && $cell->stoppage != null && $cell->stoppage != '' )
                        {
                            if( array_key_exists($cell->stoppage, $stoppageArray) )
                            {
                                $importItem['selectedStoppage'] = $stoppageArray[$cell->stoppage];
                            } else $importItem['selectedStoppage'] = 0;
                        } else $importItem['selectedStoppage'] = 0;
                        
                        if( isset( $cell->transport_vehicle ) && $cell->transport_vehicle != null && $cell->transport_vehicle != '' )
                        {
                            if( array_key_exists($cell->transport_vehicle, $ViecleArray) )
                            {
                                $importItem['selectedViecle'] = $ViecleArray[$cell->transport_vehicle];
                            } else $importItem['selectedViecle'] = 0;
                        } else $importItem['selectedViecle'] = 0;

                        if( isset( $cell->hostel ) && $cell->hostel != null && $cell->hostel != '' )
                        {
                            if( array_key_exists($cell->hostel, $HostelArray) )
                            {
                                $importItem['selectedHostel'] = $HostelArray[$cell->hostel];
                            } else $importItem['selectedHostel'] = 0;
                        } else $importItem['selectedHostel'] = 0;

                        if( isset( $cell->student_category ) && $cell->student_category != null && $cell->student_category != '' )
                        {
                            if( array_key_exists($cell->student_category, $CategoriesArray) )
                            {
                                $importItem['selectedCategory'] = $CategoriesArray[$cell->student_category];
                            } else $importItem['selectedCategory'] = 0;
                        } else $importItem['selectedCategory'] = 0;

                        if( isset( $cell->student_type ) && $cell->student_type != null && $cell->student_type != '' )
                        {
                            if( array_key_exists($cell->student_type, $TypesArray) )
                            {
                                $importItem['selectedType'] = $TypesArray[$cell->student_type];
                            } else $importItem['selectedType'] = 0;
                        } else $importItem['selectedType'] = 0;                        
    
                        $importItem['mail'] = isset( $cell->mail ) && $cell->mail != null ? ( $cell->mail == 'yes' ? true : false ) : false;
                        $importItem['sms'] = isset( $cell->sms ) && $cell->sms != null ? ( $cell->sms == 'yes' ? true : false ) : false;
                        $importItem['phone'] = isset( $cell->phone ) && $cell->phone != null ? ( $cell->phone == 'yes' ? true : false ) : false;
                        
                        $importItem['fatherName'] = isset( $cell->father_name ) && $cell->father_name != null ? $cell->father_name : "";
                        $importItem['fatherPhone'] = isset( $cell->father_mobile ) && $cell->father_mobile != null ? $cell->father_mobile : "";
                        $importItem['fatherJob'] = isset( $cell->father_job ) && $cell->father_job != null ? $cell->father_job : "";
                        $importItem['fatherQualification'] = isset( $cell->father_qualification ) && $cell->father_qualification != null ? $cell->father_qualification : "";
                        $importItem['fatherEmail'] = isset( $cell->father_email ) && $cell->father_email != null ? $cell->father_email : "";
    
                        $importItem['motherName'] = isset( $cell->mother_name ) && $cell->mother_name != null ? $cell->mother_name : "";
                        $importItem['motherPhone'] = isset( $cell->mother_mobile ) && $cell->mother_mobile != null ? $cell->mother_mobile : "";
                        $importItem['motherJob'] = isset( $cell->mother_job ) && $cell->mother_job != null ? $cell->mother_job : "";
                        $importItem['motherQualification'] = isset( $cell->mother_qualification ) && $cell->mother_qualification != null ? $cell->mother_qualification : "";
                        $importItem['motherEmail'] = isset( $cell->mother_email ) && $cell->mother_email != null ? $cell->mother_email : "";

                        $importItem['corresLine'] = isset( $cell->correspondences_line ) && $cell->correspondences_line != null ? $cell->correspondences_line : "";
                        $importItem['corresCity'] = isset( $cell->correspondences_city ) && $cell->correspondences_city != null ? $cell->correspondences_city : "";
                        $importItem['corresState'] = isset( $cell->correspondences_state ) && $cell->correspondences_state != null ? $cell->correspondences_state : "";
                        $importItem['corresPIN'] = isset( $cell->correspondences_pin_code ) && $cell->correspondences_pin_code != null ? $cell->correspondences_pin_code : "";
                        $importItem['corresCountry'] = isset( $cell->correspondences_country ) && $cell->correspondences_country != null ? $cell->correspondences_country : "";
                        $importItem['corresPhone'] = isset( $cell->correspondences_phone ) && $cell->correspondences_phone != null ? $cell->correspondences_phone : "";
                        $importItem['corresMobile'] = isset( $cell->correspondences_mobile ) && $cell->correspondences_mobile != null ? $cell->correspondences_mobile : "";
                        
                        $importItem['permaLine'] = isset( $cell->permanent_line ) && $cell->permanent_line != null ? $cell->permanent_line : "";
                        $importItem['permaCity'] = isset( $cell->permanent_city ) && $cell->permanent_city != null ? $cell->permanent_city : "";
                        $importItem['permaState'] = isset( $cell->permanent_state ) && $cell->permanent_state != null ? $cell->permanent_state : "";
                        $importItem['permaPIN'] = isset( $cell->permanent_pin_code ) && $cell->permanent_pin_code != null ? $cell->permanent_pin_code : "";
                        $importItem['permaCountry'] = isset( $cell->permanent_country ) && $cell->permanent_country != null ? $cell->permanent_country : "";
                        $importItem['permaPhone'] = isset( $cell->permanent_phone ) && $cell->permanent_phone != null ? $cell->permanent_phone : "";
                        $importItem['permaMobile'] = isset( $cell->permanent_mobile ) && $cell->permanent_mobile != null ? $cell->permanent_mobile : "";
                        
                        if( isset( $importItem['error'] ) && count($importItem['error'] ) > 0) { $dataImport['revise'][] = $importItem; }
                        else { $dataImport['ready'][] = $importItem; }
                    // }
				}

				$toReturn = array();
				$toReturn['dataImport'] = $dataImport;
				$toReturn['sections'] = $sectionsArray;
				$toReturn['classes'] = $classArray_id;
				return $toReturn;
            }
        }
        else
        {
            return json_encode( array("jsTitle" => $this->panelInit->language['Import'], "jsStatus" => "0", "jsMessage" => $this->panelInit->language['specifyFileToImport']) );
        }
    }

    public function update()
    {
        User::$withoutAppends = true;
        if( !$this->panelInit->can('students.editStudent') ) { return $this->panelInit->apiOutput(false, "Update Student", "You don't have permission to edit students"); }
        $studentId = \Input::get('studentId');
        $student = User::find( $studentId );
        if( !$student ) { return $this->panelInit->apiOutput(false, "Update Student", "Unable to read student data"); }
        $comVia = [];
        if( \Input::get('mail') === "true" ) $comVia[] = "mail";
        if( \Input::get('sms') === "true" ) $comVia[] = "sms";
        if( \Input::get('phone') === "true" ) $comVia[] = "phone";
        if( !trim( \Input::get('admissionNo') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Student admission number is missing"); }
        if( User::where('admission_number', \Input::get('admissionNo') )->where("id", "!=", $studentId)->count() > 0 ) { return $this->panelInit->apiOutput( false, "Update Student", "Student Admission number already used before."); }
        if( !trim( \Input::get('firstName') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Student first name is missing"); }
        if( !trim( \Input::get('dateOfBirth') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Student date of birth is missing"); }
        if( !trim( \Input::get('classId') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Student class is missing"); }
        if( !trim( \Input::get('sectionId') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Student section is missing"); }

        if( !trim( \Input::get('guardianType') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Guardian type is missing"); }
        if( !trim( \Input::get('guardianName') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Guardian name is missing"); }
        if( !trim( \Input::get('guardianRelation') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Guardian relation is missing"); }
        if( !trim( \Input::get('guardianPhone') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Guardian phone is missing"); }
        if( !trim( \Input::get('guardianAddress') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Guardian address is missing"); }
        if( !trim( \Input::get('guardianUsername') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Guardian username is missing"); }
        if( !trim( \Input::get('guardianPassword') ) ) { return $this->panelInit->apiOutput( false, "Update Student", "Guardian password is missing"); }
        
        if( \Input::hasFile('userPhoto') || \Input::hasFile('fatherPhoto') || \Input::hasFile('motherPhoto') )
        {
            $allowable_mimeTypes = [ "image/png", "image/jpeg", "image/pjpeg" ];

            if( \Input::hasFile('userPhoto') )
            {
                $fileInstance = \Input::file('userPhoto');
                if( !$this->panelInit->validate_upload( $fileInstance ) ) { return $this->panelInit->apiOutput(false, "Update Student", "Sorry, This File Type Is Not Permitted For Security Reasons"); }
                if( !in_array($fileInstance->getMimeType(), $allowable_mimeTypes) ) { return $this->panelInit->apiOutput(false, "Update Student", "Sorry, This File Type Is Not Permitted For Security Reasons"); }
            }
            if( \Input::hasFile('fatherPhoto') )
            {
                $fileInstance = \Input::file('fatherPhoto');
                if( !$this->panelInit->validate_upload( $fileInstance ) ) { return $this->panelInit->apiOutput(false, "Update Student", "Sorry, This File Type Is Not Permitted For Security Reasons"); }
                if( !in_array($fileInstance->getMimeType(), $allowable_mimeTypes) ) { return $this->panelInit->apiOutput(false, "Update Student", "Sorry, This File Type Is Not Permitted For Security Reasons"); }
            }
            if( \Input::hasFile('motherPhoto') )
            {
                $fileInstance = \Input::file('motherPhoto');
                if( !$this->panelInit->validate_upload( $fileInstance ) ) { return $this->panelInit->apiOutput(false, "Update Student", "Sorry, This File Type Is Not Permitted For Security Reasons"); }
                if( !in_array($fileInstance->getMimeType(), $allowable_mimeTypes) ) { return $this->panelInit->apiOutput(false, "Update Student", "Sorry, This File Type Is Not Permitted For Security Reasons"); }
            }
        }

        $fullName = trim( \Input::get('firstName') );
        if( trim( \Input::get('middleName') ) ) $fullName .= " " . trim( \Input::get('middleName') );
        if( trim( \Input::get('lastName') ) ) $fullName .= " " . trim( \Input::get('lastName') );
        $fullName = trim( $fullName );
        $guardianId = floatval( \Input::get('guardianId') );
        $oldAdmissionDate = $student->admission_date;
        $oldBirthDate = $student->dateOfBirth;

        $oldFatherInfo = json_decode( $student->father_info, true );
        if( json_last_error() != JSON_ERROR_NONE ) { $oldFatherInfo = []; }
        $oldMotherInfo = json_decode( $student->mother_info, true );
        if( json_last_error() != JSON_ERROR_NONE ) { $oldMotherInfo = []; }
        
        $father_info = []; $mother_info = [];
        $father_info["name"] = array_key_exists("name", $oldFatherInfo) ? $oldFatherInfo["name"] : "";
        $father_info["phone"] = array_key_exists("phone", $oldFatherInfo) ? $oldFatherInfo["phone"] : "";
        $father_info["job"] = array_key_exists("job", $oldFatherInfo) ? $oldFatherInfo["job"] : "";
        $father_info["qualification"] = array_key_exists("qualification", $oldFatherInfo) ? $oldFatherInfo["qualification"] : "";
        $father_info["email"] = array_key_exists("email", $oldFatherInfo) ? $oldFatherInfo["email"] : "";
        $father_info["address"] = array_key_exists("address", $oldFatherInfo) ? $oldFatherInfo["address"] : "";
        
        $mother_info["name"] = array_key_exists("name", $oldMotherInfo) ? $oldMotherInfo["name"] : "";
        $mother_info["phone"] = array_key_exists("phone", $oldMotherInfo) ? $oldMotherInfo["phone"] : "";
        $mother_info["job"] = array_key_exists("job", $oldMotherInfo) ? $oldMotherInfo["job"] : "";
        $mother_info["qualification"] = array_key_exists("qualification", $oldMotherInfo) ? $oldMotherInfo["qualification"] : "";
        $mother_info["email"] = array_key_exists("email", $oldMotherInfo) ? $oldMotherInfo["email"] : "";
        $mother_info["address"] = array_key_exists("address", $oldMotherInfo) ? $oldMotherInfo["address"] : "";
        
        // $student
        $corres_address = []; $perma_address = []; $medical = []; $previous = [];
        $corres_address['line'] = trim( \Input::get('corresAddressLine') ) ? \Input::get('corresAddressLine') : "";
        $corres_address['city'] = trim( \Input::get('corresAddressCity') ) ? \Input::get('corresAddressCity') : "";
        $corres_address['state'] = trim( \Input::get('corresAddressState') ) ? \Input::get('corresAddressState') : "";
        $corres_address['pin'] = trim( \Input::get('corresAddressPin') ) ? \Input::get('corresAddressPin') : "";
        $corres_address['country'] = trim( \Input::get('corresAddressCountry') ) ? \Input::get('corresAddressCountry') : "";
        $corres_address['phone'] = trim( \Input::get('corresAddressPhone') ) ? \Input::get('corresAddressPhone') : "";
        $corres_address['Mobile'] = trim( \Input::get('corresAddressMobile') ) ? \Input::get('corresAddressMobile') : "";

        $perma_address['line']  = trim( \Input::get('permaAddressLine') ) ? \Input::get('permaAddressLine') : "";
        $perma_address['city']  = trim( \Input::get('permaAddressCity') ) ? \Input::get('permaAddressCity') : "";
        $perma_address['state']  = trim( \Input::get('permaAddressState') ) ? \Input::get('permaAddressState') : "";
        $perma_address['pin']  = trim( \Input::get('permaAddressPin') ) ? \Input::get('permaAddressPin') : "";
        $perma_address['country']  = trim( \Input::get('permaAddressCountry') ) ? \Input::get('permaAddressCountry') : "";
        $perma_address['phone']  = trim( \Input::get('permaAddressPhone') ) ? \Input::get('permaAddressPhone') : "";
        $perma_address['Mobile']  = trim( \Input::get('permaAddressMobile') ) ? \Input::get('permaAddressMobile') : "";

        $medical['inspol'] = trim( \Input::get('inspol') ) ? \Input::get('inspol') : "";
        $medical['blood_group'] = trim( \Input::get('bloodType') ) ? \Input::get('bloodType') : "";
        $medical['weight'] = trim( \Input::get('weight') ) ? \Input::get('weight') : "";
        $medical['height'] = trim( \Input::get('height') ) ? \Input::get('height') : "";
        $medical['disab'] = trim( \Input::get('disab') ) ? \Input::get('disab') : "";
        $medical['contact'] = trim( \Input::get('contact') ) ? \Input::get('contact') : "";

        $previous['institution'] = trim( \Input::get('previousInstitution') ) ? \Input::get('previousInstitution') : "";
        $previous['class'] = trim( \Input::get('previousClass') ) ? \Input::get('previousClass') : "";
        $previous['year'] = trim( \Input::get('previousYear') ) ? \Input::get('previousYear') : "";
        $previous['percentage'] = trim( \Input::get('previousPercentage') ) ? \Input::get('previousPercentage') : "";
        $previous_data[] = $previous;
        
        if(
            trim( \Input::get('previousInstitution') ) || trim( \Input::get('previousClass') ) ||
            trim( \Input::get('previousYear') ) || trim( \Input::get('previousPercentage') )
        ) { $student->previous_data = json_encode( $previous_data ); }
        else { $student->previous_data = json_encode( [] ); }
        if( \Input::hasFile('userPhoto') )
        {
            if( trim( $student->photo ) )
            {
                if( $student->photo != "user.png" )
                {
                    $oldPhoto = $student->photo;
                    if(file_exists( uploads_config()['uploads_file_path'] . "/profile/$oldPhoto" ) )
                    {
                        unlink( uploads_config()['uploads_file_path'] . "/profile/$oldPhoto" );
                    }
                }
            }
            $fileInstance = \Input::file('userPhoto');
            $newStudentPhotoName = "profile_" . $studentId . ".jpg";
			$file = $fileInstance->move(uploads_config()['uploads_file_path'] . "/profile/$newStudentPhotoName");
			$student->photo = "profile_" . $studentId . ".jpg";
        }

        if( trim( \Input::get('fatherName') ) ) { $father_info["name"] = \Input::get('fatherName'); }
        if( trim( \Input::get('fatherPhone') ) ) { $father_info["phone"] = \Input::get('fatherPhone'); }
        if( trim( \Input::get('fatherJob') ) ) { $father_info["job"] = \Input::get('fatherJob'); }
        if( trim( \Input::get('fatherQualification') ) ) { $father_info["qualification"] = \Input::get('fatherQualification'); }
        if( trim( \Input::get('fatherEmail') ) ) { $father_info["email"] = \Input::get('fatherEmail'); }
        if( trim( \Input::get('fatherAddress') ) ) { $father_info["address"] = \Input::get('fatherAddress'); }

        if( trim( \Input::get('motherName') ) ) { $mother_info["name"] = \Input::get('motherName'); }
        if( trim( \Input::get('motherPhone') ) ) { $mother_info["phone"] = \Input::get('motherPhone'); }
        if( trim( \Input::get('motherJob') ) ) { $mother_info["job"] = \Input::get('motherJob'); }
        if( trim( \Input::get('motherQualification') ) ) { $mother_info["qualification"] = \Input::get('motherQualification'); }
        if( trim( \Input::get('motherEmail') ) ) { $mother_info["email"] = \Input::get('motherEmail'); }
        if( trim( \Input::get('motherAddress') ) ) { $mother_info["address"] = \Input::get('motherAddress'); }
        
        if( \Input::hasFile('fatherPhoto') )
        {
            if( !array_key_exists("pic", $oldFatherInfo) ) { $pic = "father.png"; }
            else { $pic = $oldFatherInfo["pic"]; }
            if( trim($pic) && $pic != "father.png" )
            {
                if(file_exists( uploads_config()['uploads_file_path'] . "/parents/$pic" ) )
                {
                    unlink( uploads_config()['uploads_file_path'] . "/parents/$pic" );
                }
            }
            $newFatherFileName = "father_" . $studentId . ".jpg";
            $fatherPhotoInstance->move(uploads_config()['uploads_file_path'] . "/parents/$newFatherFileName" );
            $father_info['pic'] = "father_" . $studentId . ".jpg";
        }

        if( \Input::hasFile('motherPhoto') )
        {
            if( !array_key_exists("pic", $oldMotherInfo) ) { $pic = "mother.png"; }
            else { $pic = $oldMotherInfo["pic"]; }
            if( trim($pic) && $pic != "mother.png" )
            {
                if(file_exists( uploads_config()['uploads_file_path'] . "/parents/$pic" ) )
                {
                    unlink( uploads_config()['uploads_file_path'] . "/parents/$pic" );
                }
            }
            $newMotherFileName = "mother_" . $studentId . ".jpg";
            $motherPhotoInstance->move(uploads_config()['uploads_file_path'] . "/parents/$newMotherFileName");
            $mother_info['pic'] = "mother_" . $studentId . ".jpg";
        }
        
        if( \Input::get('requestUnlink') == "NO" )
        {
            // Nothing really special here
        }
        else
        {
            $oldGaurdiansIds = getParentIdsFromStudentId( $studentId );
            $oldGaurdians = User::whereIn('id', $oldGaurdiansIds);
            foreach( $oldGaurdians as $oneGaurdian )
            {
                $parentOf = json_decode( $oneGaurdian['parentOf'], true );
                if( json_last_error() != JSON_ERROR_NONE ) $parentOf = [];
                $gaurdianId = $oneGaurdian['id'];
                $newWardsList = [];
                foreach( $parentOf as $wardIndex => $oneWard )
                {
                    if( array_key_exists('id', $oneWard) )
                    {
                        if( $oneWard['id'] != $studentId ) { $newWardsList[] = $oneWard; }
                    } else { continue; }
                }
                $updateGuardian = User::find( $gaurdianId );
                $updateGuardian->parentOf = json_encode( $newWardsList );
                $updateGuardian->save();
            }

            if( $guardianId != 0 )
            {
                $newGaurdian = User::find( $guardianId );
                if( !$newGaurdian ) { return $this->panelInit->apiOutput(false, "Update Student", 'Unable to get gaurdian data'); }

                if( User::where('username',  \Input::get('guardianUsername') )->where('id', '!=', $guardianId )->count() > 0){ return $this->panelInit->apiOutput(false, "Update Student", $this->panelInit->language['usernameUsed']); }
                $type = \Input::get('guardianType');

                if( trim( \Input::get('gaurdianMail') ) ) { $matchMail = \Input::get('gaurdianMail'); }
                else { $matchMail = $type == 'mother' ? \Input::get('motherEmail') : \Input::get('fatherEmail'); }
                if( trim( $matchMail ) || trim( $matchMail ) != "" )
                {
                    if( User::where('email', $matchMail )->where('id', '!=', $guardianId )->count() > 0) { return $this->panelInit->apiOutput(false, "Update Student", $this->panelInit->language['mailUsed']); }
                }
                
                $parentOf = json_decode( $newGaurdian->parentOf, true );
                if( json_last_error() != JSON_ERROR_NONE ) $parentOf = [];
                foreach( $parentOf as $wardsIndex => $oneWard ) { if( $oneWard['id'] == $studentId ) unset( $parentOf[$wardsIndex] ); }

                $siblingData = [
                    "id" => $studentId,
                    "student" => $fullName,
                    "relation" => \Input::get('guardianRelation'),
                    "job" => \Input::get('gaurdianJob'),
                    "phone" => \Input::get('guardianPhone'),
                    "address" => \Input::get('guardianAddress'),
                    "username" => \Input::get('guardianUsername')
                ];
                $parentOf[] = $siblingData;
                $parent->fullName = \Input::get('guardianName');
                $parent->username = \Input::get('guardianUsername');
                $parent->email = $matchMail;
                if( \Input::get('guardianPassword') != "dummyp@sswordText") { $parent->password = \Hash::make( \Input::get('guardianPassword') ); }
                $parent->parentOf = json_encode( $parentOf );
                $parent->mobileNo = \Input::get('guardianPhone');
                $parent->address = \Input::get('guardianAddress');
                $parent->comVia = json_encode( $comVia );
                $parent->save();
            }
        }

        if( $guardianId == 0 )
        {
            $def_role = roles::where('def_for', 'parent')->select('id');
            if( $def_role->count() == 0 ) { return $this->panelInit->apiOutput(false, "Update Student", 'No default role assigned for parents, Please contact administartor'); }
            $def_role = $def_role->first();
            if( User::where('username',  \Input::get('guardianUsername') )->count() > 0){ return $this->panelInit->apiOutput(false, "Update Student", $this->panelInit->language['usernameUsed']); }
            $type = \Input::get('guardianType');

            if( trim( \Input::get('gaurdianMail') ) ) { $matchMail = \Input::get('gaurdianMail'); }
            else { $matchMail = $type == 'mother' ? \Input::get('motherEmail') : \Input::get('fatherEmail'); }
            if( trim( $matchMail ) || trim( $matchMail ) != "" )
            {
                if( User::where('email', $matchMail )->count() > 0) { return $this->panelInit->apiOutput(false, "Update Student", $this->panelInit->language['mailUsed']); }
            }
            $siblingData = [
                "id" => $studentId,
                "student" => $fullName,
                "relation" => \Input::get('guardianRelation'),
                "job" => \Input::get('gaurdianJob'),
                "phone" => \Input::get('guardianPhone'),
                "address" => \Input::get('guardianAddress'),
                "username" => \Input::get('guardianUsername')
            ];
            $parentOf[] = $siblingData;
    
            $sibilings = [];
            $sibilings[] = $siblingData;
            $parantUser = new User();
            $parantUser->username = \Input::get('guardianUsername');
            $parantUser->email = $matchMail;
            $parantUser->fullName = \Input::get('guardianName');
            $parantUser->password = \Hash::make( \Input::get('guardianPassword') );
            $parantUser->role = "parent";
            $parantUser->mobileNo = \Input::get('guardianPhone');
            $parantUser->address = \Input::get('guardianAddress');
            $parantUser->comVia = json_encode( $comVia );
            $parantUser->account_active = "1";
            $parantUser->role_perm = $def_role->id;
            $parantUser->parentOf = json_encode( $sibilings );
            $parantUser->save();
            user_log('Parents', 'create', $parantUser->fullName);
        }

        $student->username = \Input::get('admissionNo');
        $student->password = \Hash::make( \Input::get('admissionNo') . '123' );
        $student->fullName = $fullName;
        $student->studentClass = \Input::get('classId');
        $student->studentSection = \Input::get('sectionId');
        $student->comVia = json_encode( $comVia );
        $student->admission_number = \Input::get('admissionNo');
        if( trim( \Input::get('admissionDate') ) )
        {
            $parseDate = [];
            $parseDate = explode('/', \Input::get('admissionDate'));
            if( count( $parseDate ) != 3 ) { return $this->panelInit->apiOutput(false, "Update Student", "Admission Date has wrong format"); }
            $writeDate = $parseDate[2] . "-" . $parseDate[1] . "-" . $parseDate[0];
            $student->admission_date = strtotime( $writeDate );
        } else $student->admission_date = $oldAdmissionDate;

        if( \Input::get('dateOfBirth') )
        {
            $parseDate = [];
            $parseDate = explode('/', \Input::get('dateOfBirth'));
            if( count( $parseDate ) != 3 ) { return $this->panelInit->apiOutput(false, "Update Student", "Date of Birth has wrong format"); }
            $writeDate = $parseDate[2] . "-" . $parseDate[1] . "-" . $parseDate[0];
            $student->birthday = strtotime( $writeDate );
        } else { $student->birthday = $oldBirthDate; }
        
        if( trim( \Input::get('dateOfBirth') ) ) { $student->birthday = toUnixStamp( \Input::get('dateOfBirth') ); }
        if( trim( \Input::get('gender') ) ) { $student->gender = \Input::get('gender'); }
        if( trim( \Input::get('birthPlace') ) ) { $student->birthPlace = \Input::get('birthPlace'); }
        if( trim( \Input::get('nationality') ) ) { $student->nationality = \Input::get('nationality'); }
        if( trim( \Input::get('religion') ) ) { $student->religion = \Input::get('religion'); }
        if( trim( \Input::get('studentCategory') ) ) { $student->std_category = \Input::get('studentCategory'); }
        if( trim( \Input::get('studentType') ) ) { $student->studentType = \Input::get('studentType'); }
        if( trim( \Input::get('rollNo') ) ) { $student->studentRollId = \Input::get('rollNo'); }
        if( trim( \Input::get('bioId') ) )
        {
            $student->bioId = \Input::get('bioId');
            $student->biometric_id = \Input::get('bioId');
        }
        if( \Input::has('corresAddressLine') ) { $student->address = \Input::get('corresAddressLine'); }
        elseif( \Input::has('permaAddressLine') ) { $student->address = \Input::get('permaAddressLine'); }

        if( \Input::has('corresAddressPhone') ) { $student->phoneNo = \Input::get('corresAddressPhone'); }
        elseif( \Input::has('permaAddressPhone') ) { $student->phoneNo = \Input::get('permaAddressPhone'); }

        if( \Input::has('corresAddressMobile') ) { $student->mobileNo = \Input::get('corresAddressMobile'); }
        elseif( \Input::has('permaAddressMobile') ) { $student->mobileNo = \Input::get('permaAddressMobile'); }
        
        if( \Input::has('stoppage') ) { $student->transport = \Input::get('stoppage'); }
        if( \Input::has('transport') ) { $student->transport_vehicle = \Input::get('transport'); }
        if( \Input::has('hostel') ) { $student->hostel = \Input::get('hostel'); }
        if( \Input::has('room') ) { $student->room = \Input::get('room'); }
        
        $student->corres_address = json_encode( $corres_address );
        $student->perma_address = json_encode( $perma_address );
        $student->medical = json_encode( $medical );
        $student->save();
        user_log('Students', 'update', $student->fullName);

        $studentAcademicYears = student_academic_years::where('studentId', $studentId)->where('academicYearId', $this->panelInit->selectAcYear)->first();
        if( !$studentAcademicYears )
        {
            $studentAcademicYears = new student_academic_years();
            $studentAcademicYears->studentId = $studentId;
		    $studentAcademicYears->academicYearId = $this->panelInit->selectAcYear;
        }
        $studentAcademicYears->classId = \Input::get('classId');
        $studentAcademicYears->sectionId = \Input::get('sectionId');
        $studentAcademicYears->save();
        $class = MClass::select('id', 'className as name')->where('id', $student->studentClass)->first();
        $section = \sections::select('id', 'sectionName as name')->where('id', $student->studentSection)->first();
        $stdType = StudentType::select('id', 'title as name')->where('id', $student->studentType)->first();
        $stdCat = StudentCategory::select('id', 'cat_title as name')->where('id', $student->std_category)->first();
        $gaurdianIds = User::getParentIdsFromStudentId($studentId);
        $gaurdians = User::select('id', 'fullName as name', 'parentOf', 'address', 'username', 'mobileNo', 'email')->whereIn('id', $gaurdianIds)->get()->toArray();
        $studentInfo['main'] = [
            "id" => $studentId,
            "photo" => trim( $student->photo ) ? $student->photo : "user.png",
            "name" => $student->fullName,
            "adm" => $student->admission_number,
            "roll" => $student->studentRollId,
            "bio" => $student->biometric_id,
            "class" =>  !$class ? "" : $class->name,
            "section" => !$section ? "" : $section->name,
            "classId" => $student->studentClass,
            "sectionId" => $student->studentSection,
        ];

        $medical = json_decode( $student->medical, true );
        if( json_last_error() != JSON_ERROR_NONE ) $medical = [];
        $blood_group = array_key_exists("blood_group", $medical) ? $medical['blood_group'] : "";
        if( $blood_group == "? undefined:undefined ?" ) $blood_group = "-";
        $previous_data = json_decode( $student->previous_data, true );
        if( json_last_error() != JSON_ERROR_NONE ) $previous_data[0] = [];
        if( count($previous_data) == 0 ) { $previous_data[0] = []; }
        $previous_data = $previous_data[0];

        $db_gender = strtolower($student->gender);
        $fatherInfo = json_decode( $student->father_info, true ); if( json_last_error() != JSON_ERROR_NONE ) $fatherInfo = [];
        $motherInfo = json_decode( $student->mother_info, true ); if( json_last_error() != JSON_ERROR_NONE ) $motherInfo = [];
        $perma_address = json_decode( $student->perma_address, true ); if( json_last_error() != JSON_ERROR_NONE ) $perma_address = [];
        $corres_address = json_decode( $student->corres_address, true ); if( json_last_error() != JSON_ERROR_NONE ) $corres_address = [];
        
        $state = "-";
        if( array_key_exists('state', $corres_address) ) { if( trim( $corres_address['state'] ) ) { $state = $corres_address['state']; } }
        if( $state == "-" )
        {
            if( array_key_exists('state', $perma_address) ) { if( trim( $perma_address['state'] ) ) { $state = $perma_address['state']; } }
        }
        $country = "-";
        if( array_key_exists('country', $corres_address) ) { if( trim( $corres_address['country'] ) ) { $country = $corres_address['country']; } }
        if( $country == "-" )
        {
            if( array_key_exists('country', $perma_address) ) { if( trim( $perma_address['country'] ) ) { $country = $perma_address['country']; } }
        }
        if( $country == "-" ) { $country = "India"; }
        
        $gender = $db_gender == "m" || $db_gender == "male" ? "Male" : ( $db_gender == "f" || $db_gender == "female" ? "Female" : "-" );
        $studentInfo['profile'] = [
            "username" => $student->username,                       "gender" => $gender,
            "type" => $stdType ? $stdType->name : "Default",        "religion" => $student->religion ? $student->religion : "-",
            "birthday" => date("d M Y", $student->birthday),        "phone" => $student->phoneNo,
            "blood" => $blood_group,                                "address" => $student->address ? $student->address : "-",
            "email" => $student->email ? $student->email : "-",     "state" => $state,
            "category" => $stdCat ? $stdCat->name : "Default",      "country" => $country
        ];
        $stdName = explode(' ', $student->fullName);
        if( count($stdName) > 3 )
        {
            $studentInfo['profile']['first'] = $stdName[0]; $studentInfo['profile']['middle'] = $stdName[1];
            for( $i = 2; $i <= count($stdName); $i++ ) { $studentInfo['profile']['last'] .= $stdName[$i]; }
        }
        elseif( count($stdName) == 3 )
        {
            $studentInfo['profile']['first'] = $stdName[0]; $studentInfo['profile']['middle'] = $stdName[1];
            $studentInfo['profile']['last'] = $stdName[2];
        }
        elseif( count($stdName) == 2 )
        {
            $studentInfo['profile']['first'] = $stdName[0]; $studentInfo['profile']['last'] = $stdName[1];
            $studentInfo['profile']['middle'] = "";
        }
        else
        {
            $studentInfo['profile']['first'] = $stdName[0]; $studentInfo['profile']['middle'] = ""; $studentInfo['profile']['last'] = "";
        }
        $admDate = trim( $student->admission_date ) ? ( date('d/m/Y', $student->admission_date) ) : "";
        $studentInfo['main']['adm_date'] = $admDate;
        $studentInfo['main']['dob'] = date('d/m/Y', $student->birthday);
        $studentInfo['main']['gender'] = $db_gender == "m" || $db_gender == "male" ? "male" : ( $db_gender == "f" || $db_gender == "female" ? "female" : "0" );
        $studentInfo['main']['birthPlace'] = trim( $student->birthPlace ) ? $student->birthPlace : "";
        $studentInfo['main']['nationality'] = trim( $student->nationality ) ? $student->nationality : "";
        $studentInfo['main']['std_category'] = $student->std_category;
        $studentInfo['main']['std_type'] = $student->studentType;

        $studentInfo['parents'] = [
            'sibling' => [],
            'father' => [
                'name' => array_key_exists('name', $fatherInfo) ? $fatherInfo['name'] : "",
                'job' => array_key_exists('job', $fatherInfo) ? $fatherInfo['job'] : "",
                'phone' => array_key_exists('phone', $fatherInfo) ? $fatherInfo['phone'] : "",
                'qualification' => array_key_exists('qualification', $fatherInfo) ? $fatherInfo['qualification'] : "",
                'address' => array_key_exists('address', $fatherInfo) ? $fatherInfo['address'] : "",
                'email' => array_key_exists('email', $fatherInfo) ? $fatherInfo['email'] : "",
            ],
            'mother' => [
                'name' => array_key_exists('name', $motherInfo) ? $motherInfo['name'] : "",
                'job' => array_key_exists('job', $motherInfo) ? $motherInfo['job'] : "",
                'phone' => array_key_exists('phone', $motherInfo) ? $motherInfo['phone'] : "",
                'qualification' => array_key_exists('qualification', $fatherInfo) ? $fatherInfo['qualification'] : "",
                'address' => array_key_exists('address', $motherInfo) ? $motherInfo['address'] : "",
                'email' => array_key_exists('email', $motherInfo) ? $motherInfo['email'] : ""
            ],
            'gaurdian' => []
        ];
        if( array_key_exists('pic', $fatherInfo) )
        {
            $pic = trim( $fatherInfo['pic'] );
            if( $pic == "" || $pic == NULL ) { $studentInfo['parents']['father']['pic'] = "father.png"; }
            else
            {
                if( file_exists( uploads_config()['uploads_file_path'] . "/parents/$pic" ) )
                {
                    $studentInfo['parents']['father']['pic'] = $pic;
                } else { $studentInfo['parents']['father']['pic'] = "father.png"; }
            }
        } else { $studentInfo['parents']['father']['pic'] = "father.png"; }

        if( array_key_exists('pic', $motherInfo) )
        {
            $pic = trim( $motherInfo['pic'] );
            if( $pic == "" || $pic == NULL ) { $studentInfo['parents']['mother']['pic'] = "mother.png"; }
            else
            {
                if( file_exists( uploads_config()['uploads_file_path'] . "/parents/$pic" ) )
                {
                    $studentInfo['parents']['mother']['pic'] = $pic;
                } else { $studentInfo['parents']['mother']['pic'] = "mother.png"; }
            }
        } else { $studentInfo['parents']['mother']['pic'] = "mother.png"; }

        if( array_key_exists("line", $corres_address) )
        {
            $gaurdainAddress = trim( $corres_address["line"] ) ? trim( $corres_address["line"] ) : NULL;
        } else $gaurdainAddress = NULL;
        
        $siblingIds = [];
        foreach( $gaurdians as $oneGaurdian )
        {
            $gaurdianId = $oneGaurdian['id'];
            $wardInfo = json_decode($oneGaurdian['parentOf'], true);
            $addressOfGaurdian = $gaurdainAddress != NULL ? $gaurdainAddress : ( $oneGaurdian['address'] ? $oneGaurdian['address'] : "" );
            if( json_last_error() != JSON_ERROR_NONE ) $wardInfo = [];
            if( count( $wardInfo ) > 0 )
            {
                foreach( $wardInfo as $oneWard )
                {
                    $wardId = $oneWard['id'];
                    if( $wardId == $studentId )
                    {
                        $relation = $oneWard['relation'];
                        $proffesion = array_key_exists('job', $oneWard) ? $oneWard['job'] : "";
                        if( array_key_exists('phone', $oneWard) )
                        {
                            if( trim( $oneWard["phone"] ) )
                            {
                                $gaurdainMobile = $oneWard["phone"];
                            } else $gaurdainMobile = $oneGaurdian['mobileNo'];
                        } else $gaurdainMobile = $oneGaurdian['mobileNo'];

                        if( array_key_exists('address', $oneWard) )
                        {
                            if( trim( $oneWard["address"] ) )
                            {
                                $gaurdain_address = $oneWard["address"];
                            } else $gaurdain_address = $addressOfGaurdian;
                        } else $gaurdain_address = $addressOfGaurdian;

                        if( array_key_exists('username', $oneWard) )
                        {
                            if( trim( $oneWard["username"] ) )
                            {
                                $gaurdainUsername = $oneWard["username"];
                            } else $gaurdainUsername = $oneGaurdian['username'];
                        } else $gaurdainUsername = $oneGaurdian['username'];
                        // 
                        $relationId = strtolower($relation) == "father" ? "father" : ( strtolower($relation) == "mother" ? "mother" : "others" );
                        $studentInfo['parents']['gaurdian']['id'] = $gaurdianId;
                        $studentInfo['parents']['gaurdian']['name'] = $oneGaurdian['name'];
                        $studentInfo['parents']['gaurdian']['relation'] = $relation;
                        $studentInfo['parents']['gaurdian']['job'] = $proffesion;
                        $studentInfo['parents']['gaurdian']['phone'] = $gaurdainMobile;
                        $studentInfo['parents']['gaurdian']['address'] = $gaurdain_address;
                        $studentInfo['parents']['gaurdian']['email'] = $oneGaurdian['email'];
                        $studentInfo['parents']['gaurdian']['username'] = $gaurdainUsername;
                        $studentInfo['parents']['gaurdian']['relationId'] = $relationId;
                    }
                    else { $siblingIds[] = $wardId; }
                }
            }
        }

        if( count( $siblingIds ) > 0 )
        {
            $getSiblings = User::select('id','fullName as name', 'admission_number as admission')->whereIn('id', $siblingIds)->get()->toArray();
            foreach( $getSiblings as $oneSibling )
            {
                $studentInfo['parents']['sibling'][] = ['id' => $oneSibling['id'], 'name' => $oneSibling['name'], 'admission' => $oneSibling['admission']];
            }
        }
        $studentInfo['previous'] = [ "institution" => "", "class" => "", "year" => "", "percentage" => ""];
        $studentInfo['medical'] = ["inspol" => "", "blood_group" => "", "weight" => "", "height" => "", "disab" => "", "contact" => ""];
        $studentInfo['corres'] = ["line" => "", "city" => "", "state" => "", "pin" => "", "country" => "", "phone" => "", "Mobile" => ""];
        $studentInfo['perma'] = ["line" => "", "city" => "", "state" => "", "pin" => "", "country" => "", "phone" => "", "Mobile" => ""];
        $studentInfo['assigns'] = ["transport" => "", "stoppage" => "", "hostel" => "", "room" => "", "mail" => false, "sms" => false, "phone" => false ];
        $studentInfo['assigns']['transport'] = $student->transport_vehicle;
        $studentInfo['assigns']['stoppage'] = $student->transport;
        $studentInfo['assigns']['hostel'] = $student->hostel;
        $studentInfo['assigns']['room'] = $student->room ? intval( $student->room ) : "";
        $comVia = json_decode( $student->comVia, true ); if( json_last_error() != JSON_ERROR_NONE ) { $comVia = []; }
        if( in_array("mail", $comVia) ) { $studentInfo['assigns']['mail'] = true; }
        if( in_array("sms", $comVia) ) { $studentInfo['assigns']['sms'] = true; }
        if( in_array("phone", $comVia) ) { $studentInfo['assigns']['phone'] = true; }
        
        if( array_key_exists('institution', $previous_data) ) $studentInfo['previous']['institution'] = $previous_data["institution"];
        if( array_key_exists('class', $previous_data) ) $studentInfo['previous']['class'] = $previous_data["class"];
        if( array_key_exists('year', $previous_data) ) $studentInfo['previous']['year'] = $previous_data["year"];
        if( array_key_exists('percentage', $previous_data) ) $studentInfo['previous']['percentage'] = $previous_data["percentage"];

        if( $blood_group != "-" ) $studentInfo['medical']['blood_group'] = $blood_group;
        if( array_key_exists('inspol', $medical) ) $studentInfo['medical']['inspol'] = $medical["inspol"];
        if( array_key_exists('weight', $medical) ) $studentInfo['medical']['weight'] = $medical["weight"];
        if( array_key_exists('height', $medical) ) $studentInfo['medical']['height'] = $medical["height"];
        if( array_key_exists('disab', $medical) ) $studentInfo['medical']['disab'] = $medical["disab"];
        if( array_key_exists('contact', $medical) ) $studentInfo['medical']['contact'] = $medical["contact"];
        if( trim( $studentInfo['medical']['weight'] ) ) $studentInfo['medical']['weight'] = floatval( $studentInfo['medical']['weight'] );
        if( trim( $studentInfo['medical']['height'] ) ) $studentInfo['medical']['height'] = floatval( $studentInfo['medical']['height'] );

        if( array_key_exists('line', $corres_address) ) $studentInfo['corres']['line'] = $corres_address["line"];
        if( array_key_exists('city', $corres_address) ) $studentInfo['corres']['city'] = $corres_address["city"];
        if( array_key_exists('state', $corres_address) ) $studentInfo['corres']['state'] = $corres_address["state"];
        if( array_key_exists('pin', $corres_address) ) $studentInfo['corres']['pin'] = $corres_address["pin"];
        if( array_key_exists('country', $corres_address) ) $studentInfo['corres']['country'] = $corres_address["country"];
        if( array_key_exists('phone', $corres_address) ) $studentInfo['corres']['phone'] = $corres_address["phone"];
        if( array_key_exists('Mobile', $corres_address) ) $studentInfo['corres']['Mobile'] = $corres_address["Mobile"];
        
        if( array_key_exists('line', $perma_address) ) $studentInfo['perma']['line'] = $perma_address["line"];
        if( array_key_exists('city', $perma_address) ) $studentInfo['perma']['city'] = $perma_address["city"];
        if( array_key_exists('state', $perma_address) ) $studentInfo['perma']['state'] = $perma_address["state"];
        if( array_key_exists('pin', $perma_address) ) $studentInfo['perma']['pin'] = $perma_address["pin"];
        if( array_key_exists('country', $perma_address) ) $studentInfo['perma']['country'] = $perma_address["country"];
        if( array_key_exists('phone', $perma_address) ) $studentInfo['perma']['phone'] = $perma_address["phone"];
        if( array_key_exists('Mobile', $perma_address) ) $studentInfo['perma']['Mobile'] = $perma_address["Mobile"];

        $toReturn["status"] = "success";
        $toReturn["title"] = "Update Student";
        $toReturn["message"] = "Student updated successfully";
        $toReturn["studentInfo"] = $studentInfo;
        return $toReturn;
    }

    public function imported()
    {
        User::$withoutAppends = true;
        if( \Input::has('importReview') )
        {
            $completes = 0;
            foreach( \Input::get('importReview') as $student )
            {
                $comVia = [];
                if( $student['mail'] == true ) { $comVia[] = "mail"; }
                if( $student['sms'] == true ) { $comVia[] = "sms"; }
                if( $student['phone'] == true ) { $comVia[] = "phone"; }

                
                if( !array_key_exists('admissionNo', $student) ) continue;
                if( !array_key_exists('dateOfBirth', $student) ) continue;
                if( !array_key_exists('studentClass', $student) ) continue;
                if( !array_key_exists('studentSection', $student) ) continue;
                
                if( User::where('admission_number', $student['admissionNo'] )->count() > 0 ) continue;
                if( !toUnixStamp( $student['dateOfBirth'] ) ) continue;
                $chkClass = MClass::find( $student['studentClass'] );
                $chkSection = Section::find( $student['studentSection'] );
                if( !$chkClass ) continue;
                if( !$chkSection ) continue;
                
                if( !array_key_exists('firstName', $student) ) continue;
                if( !array_key_exists('guardianId', $student) && !array_key_exists('guardianUsername', $student) && !array_key_exists('guardianPassword', $student) ) continue;
                if( $student['guardianId'] == NULL )
                {
                    if( !array_key_exists('guardianUsername', $student) ) continue;
                    if( !array_key_exists('guardianRelation', $student) ) continue;
                    // if( !array_key_exists('guardianEmail', $student) ) continue;
                    if( !array_key_exists('guardianName', $student) ) continue;
                    if( !array_key_exists('guardianPhone', $student) ) continue;
                    if( !array_key_exists('guardianAddress', $student) ) continue;
                    if( !array_key_exists('guardianPassword', $student) ) continue;

                    // Get the default role
                    $def_role = roles::where('def_for','parent')->select('id');
                    if( $def_role->count() == 0 ) continue;
                    $def_role = $def_role->first();
                    if( User::where('username',  $student['guardianUsername'] )->count() > 0) continue;
                    if( array_key_exists('guardianEmail', $student) )
                    {
                        if( User::where('email', $student['guardianEmail'] )->count() > 0) continue;
                    }
                    
                    $guardianRelation = $student['guardianRelation'];
                    $parantUser = new User();
                    $parantUser->username = $student['guardianUsername'];
                    if( array_key_exists('guardianEmail', $student) )
                    {
                        $parantUser->email = $student['guardianEmail'];
                    }
                    $parantUser->fullName = $student['guardianName'];
                    $parantUser->password = \Hash::make( $student['guardianPassword'] );
                    $parantUser->role = "parent";
                    $parantUser->mobileNo = $student['guardianPhone'];
                    $parantUser->comVia = json_encode( $comVia );
                    $parantUser->account_active = "1";
                    $parantUser->role_perm = $def_role->id;
                    $parantUser->save();
                    $parentId = $parantUser->id;
                    user_log('Parents', 'create', $parantUser->fullName);
                }
                else
                {
                    $getStudent = User::where('role','student');
                    $getStudent = $getStudent->where('admission_number', $student['guardianId']);
                    $isExsist = $getStudent->count();
                    $getStudent = $getStudent->first();
                    if( $isExsist == 0 ) continue;
                    
                    $studentId = $getStudent->id;
                    $guardian = User::where('parentOf', 'like', '%"id":' . $studentId . '%')->first();
                    if( !$guardian ) continue;
                    $parentId = $guardian->id;
                    $parentOf = json_decode( $guardian->parentOf, true );
                    if( json_last_error() != JSON_ERROR_NONE ) continue;

                    $guardianRelation = $parentOf[0]['relation'];
                }
                // Get the default role
                $def_student_role = roles::where('def_for','student')->select('id');
                if( $def_student_role->count() == 0 ) continue;
                $def_student_role = $def_student_role->first();
                $fullName = trim( $student['fullName'] );
        
                $impotedStudent = new User();
                $impotedStudent->username = $student['admissionNo'];
                $impotedStudent->password = \Hash::make( $student['admissionNo'] . '123' );
                $impotedStudent->fullName = $fullName;
                $impotedStudent->role = "student";
                $impotedStudent->studentAcademicYear = $this->panelInit->selectAcYear;
                $impotedStudent->studentClass = $student['studentClass'];
                $impotedStudent->studentSection = $student['studentSection'];
                $impotedStudent->comVia = json_encode( $comVia );
                $impotedStudent->admission_number = $student['admissionNo'];
                if( array_key_exists('admissionDate', $student) )
                {
                    $impotedStudent->admission_date = date('Y-m-d', strtotime( $student['admissionDate']['date'] ));
                }
                if( toUnixStamp( $student['dateOfBirth'] ) ) { $impotedStudent->birthday = toUnixStamp( $student['dateOfBirth'] ); }
                if( array_key_exists('gender', $student) ) { $impotedStudent->gender = strtolower( $student['gender'] ); }
                if( array_key_exists('birthPlace', $student) ) { $impotedStudent->birthPlace = $student['birthPlace']; }
                if( array_key_exists('nationality', $student) ) { $impotedStudent->nationality = $student['nationality']; }
                if( array_key_exists('religion', $student) ) { $impotedStudent->religion = $student['religion']; }
                // if( array_key_exists('studentCategory', $student) ) { $impotedStudent->std_category = $student['studentCategory']; }
                // if( array_key_exists('studentType', $student) ) { $impotedStudent->studentType = $student['studentType']; }
                if( array_key_exists('selectedCategory', $student) ) { $impotedStudent->std_category = $student['selectedCategory']; }
                if( array_key_exists('selectedType', $student) ) { $impotedStudent->studentType = $student['selectedType']; }
                                
                if( array_key_exists('rollNo', $student) ) { $impotedStudent->studentRollId = $student['rollNo']; }
                if( array_key_exists('bioId', $student) ) { $impotedStudent->bioId = $student['bioId']; }
                if( array_key_exists('bioId', $student) ) { $impotedStudent->biometric_id = $student['bioId']; }


                $corres_address = []; $perma_address = [];
                if( array_key_exists('corresLine', $student) )
                {
                    if( trim( $student['corresLine'] ) != "" ) { $impotedStudent->address = trim( $student['corresLine'] ); }
                    elseif( array_key_exists('permaLine', $student) )
                    {
                        if( trim( $student['permaLine'] ) != "" ) { $impotedStudent->address = trim( $student['permaLine'] ); }
                    }
                }
                elseif( array_key_exists('permaLine', $student) )
                {
                    if( trim( $student['permaLine'] ) != "" ) { $impotedStudent->address = trim( $student['permaLine'] ); }
                }

                if( array_key_exists('corresPhone', $student) )
                {
                    if( trim( $student['corresPhone'] ) != "" ) { $impotedStudent->phoneNo = trim( $student['corresPhone'] ); }
                    elseif( array_key_exists('permaPhone', $student) )
                    {
                        if( trim( $student['permaPhone'] ) != "" ) { $impotedStudent->phoneNo = trim( $student['permaPhone'] ); }
                    }
                }
                elseif( array_key_exists('permaPhone', $student) )
                {
                    if( trim( $student['permaPhone'] ) != "" ) { $impotedStudent->phoneNo = trim( $student['permaPhone'] ); }
                }

                if( array_key_exists('corresMobile', $student) )
                {
                    if( trim( $student['corresMobile'] ) != "" ) { $impotedStudent->mobileNo = trim( $student['corresMobile'] ); }
                    elseif( array_key_exists('permaMobile', $student) )
                    {
                        if( trim( $student['permaMobile'] ) != "" ) { $impotedStudent->mobileNo = trim( $student['permaMobile'] ); }
                    }
                }
                elseif( array_key_exists('permaMobile', $student) )
                {
                    if( trim( $student['permaMobile'] ) != "" ) { $impotedStudent->mobileNo = trim( $student['permaMobile'] ); }
                }

                $corres_address['line'] = $student['corresLine'];
                $corres_address['city'] = $student['corresCity'];
                $corres_address['state'] = $student['corresState'];
                $corres_address['pin'] = $student['corresPIN'];
                $corres_address['country'] = $student['corresCountry'];
                $corres_address['phone'] = $student['corresPhone'];
                $corres_address['Mobile'] = $student['corresMobile'];

                $perma_address['line'] = $student['permaLine'];
                $perma_address['city'] = $student['permaCity'];
                $perma_address['state'] = $student['permaState'];
                $perma_address['pin'] = $student['permaPIN'];
                $perma_address['country'] = $student['permaCountry'];
                $perma_address['phone'] = $student['permaPhone'];
                $perma_address['Mobile'] = $student['permaMobile'];

                $impotedStudent->corres_address = json_encode( $corres_address );
                $impotedStudent->perma_address = json_encode( $perma_address );
                if( array_key_exists('selectedStoppage', $student ) ) { if( $student['selectedStoppage'] != 0 ) { $impotedStudent->transport = $student['selectedStoppage']; } }
                if( array_key_exists('selectedViecle', $student ) ) { if( $student['selectedViecle'] != 0 ) { $impotedStudent->transport_vehicle = $student['selectedViecle']; } }
                if( array_key_exists('selectedHostel', $student ) ) { if( $student['selectedHostel'] != 0 ) { $impotedStudent->hostel = $student['selectedHostel']; } }
                
                $previousInstitution = array_key_exists('previousInstitution', $student) ? $student['previousInstitution'] : NULL;
                $previousClass = array_key_exists('previousClass', $student) ? $student['previousClass'] : NULL;
                $previousYear = array_key_exists('previousYear', $student) ? $student['previousYear'] : NULL;
                $previousPercentage = array_key_exists('previousPercentage', $student) ? $student['previousPercentage'] : NULL;
                
                $previous = [];
                $previous['institution'] = $student['previousInstitution'];
                $previous['class'] = $student['previousClass'];
                $previous['year'] = $student['previousYear'];
                $previous['percentage'] = $student['previousPercentage'];
                $previous_data[] = $previous;
                if( $previousInstitution || $previousClass || $previousYear || $previousPercentage )
                { $impotedStudent->previous_data = json_encode( $previous_data ); }
        
                $impotedStudent->isLeaderBoard = "";
                $impotedStudent->account_active = "1";
                $impotedStudent->role_perm = $def_student_role->id;
                $impotedStudent->save();
                
                $studentId = $impotedStudent->id;
                $currentStudent = User::find( $studentId );
                
                $father_info['name'] = array_key_exists('fatherName', $student) ? $student['fatherName'] : "";
                $father_info['phone'] = array_key_exists('fatherPhone', $student) ? $student['fatherPhone'] : "";
                $father_info['job'] = array_key_exists('fatherJob', $student) ? $student['fatherJob'] : "";
                $father_info['qualification'] = array_key_exists('fatherQualification', $student) ? $student['fatherQualification'] : "";
                $father_info['email'] = array_key_exists('fatherEmail', $student) ? $student['fatherEmail'] : "";
                $father_info['pic'] = "";
                
                $mother_info['name'] = array_key_exists('motherName', $student) ? $student['motherName'] : "";
                $mother_info['phone'] = array_key_exists('motherPhone', $student) ? $student['motherPhone'] : "";
                $mother_info['job'] = array_key_exists('motherJob', $student) ? $student['motherJob'] : "";
                $mother_info['qualification'] = array_key_exists('motherQualification', $student) ? $student['motherQualification'] : "";
                $mother_info['email'] = array_key_exists('motherEmail', $student) ? $student['motherEmail'] : "";
                $mother_info['pic'] = "";
                
                $currentStudent->father_info = json_encode( $father_info );
                $currentStudent->mother_info = json_encode( $mother_info );
                
                $currentStudent->save();
                user_log('Students', 'create', $impotedStudent->fullName);
                
                $parentOf = [];
                $siblingData = [
                    'id' => $studentId,
                    'student' => $impotedStudent->fullName,
                    'relation' => $guardianRelation,
                ];
                $parent = User::find( $parentId );
                $parentOf = json_decode( $parent->parentOf, true );
                if( json_last_error() != JSON_ERROR_NONE ) $parentOf = [];
                $parentOf[] = $siblingData;
                $parent->parentOf = json_encode( $parentOf );
                $parent->save();

                // auto invoicing
                    if( array_key_exists('selectedStoppage', $student ) )
                    {
                        if( $student['selectedStoppage'] != 0 )
                        {
                            $transportation = transportation::find( $student['selectedStoppage'] );
                            if( $transportation )
                            {
                                for( $i= 1 ; $i <= 12 ; $i++ )
                                {
                                    $start = $this->panelInit->settingsArray['invoice_sc_'.$i];
                                    $due = $this->panelInit->settingsArray['invoice_sc_due_'.$i];
                                    $xx = $this->panelInit->settingsArray['months_'.$i];
                                    $fine_tra = $this->panelInit->settingsArray['fine_tra'];
                                    if( !empty($start) && !empty($due) && !empty($xx) && strlen($start) > 6 && strlen($due) > 6 && $xx > 0 )
                                    {
                                        $payments = new payments();
                                        $payments->paymentTitle = "Transport Fee";
                                        $payments->paymentDescription = $transportation->transportTitle . " fee of " . $xx . " months";
                                        $payments->paymentStudent = $impotedStudent->id;
                                        $paymentRows = array();
                                        $payments->paymentRows = json_encode($paymentRows);
                                        $payments->paymentAmount = $transportation->transportFare * $xx;
                                        $payments->paymentDiscounted = $transportation->transportFare * $xx;
                                        $payments->paymentDate = toUnixStamp($start);
                                        $payments->dueDate = toUnixStamp($due);
                                        $payments->paymentUniqid = uniqid();
                                        $payments->paymentStatus = "0";
                                        $payments->fine_amount = $fine_tra;
                                        $payments->save();
                                    }
                                }
                            }
                        }
                    }
                    $studentData = [];
                    $studentData['type_id'] = array_key_exists('selectedType', $student) ? $student['selectedType'] : 0;
                    $studentData['class_id'] = $student['class'];
                    $studentData['section_id'] = $student['section'];

                    // Get payment students ----------------------------------------------
                        $students = User::where('role','student')->where('activated','1');
                        if($studentData['class_id'] > 0) { $students = $students->where('studentClass', $studentData['class_id']); }
                        if($studentData['section_id'] > 0) { $students = $students->where('studentSection', $studentData['section_id']); }
                        if($studentData['type_id'] > 0) { $students = $students->where('studentType', $studentData['type_id']); }
                        $paymentStudent = $students->distinct()->pluck('id')->toArray();
                    // end Get payment students -------------------------------------------
                    if( count( $paymentStudent ) )
                    {
                        $bulk_fees = payments::whereIn('paymentStudent', $paymentStudent)
                            ->where('paymentRows', '!=', '[]')
                            ->groupBy('paymentTitle', 'paymentDate', 'dueDate')
                            ->select('paymentTitle', 'paymentAmount', 'paymentRows', 'paymentDate', 'dueDate', 'fine_amount')
                            ->get()
                            ->toArray();
                        
                        if( count( $bulk_fees ) )
                        {
                            foreach( $bulk_fees as $fee )
                            {
                                $payment = new payments;
                                $payment->paymentTitle = $fee['paymentTitle'];
                                $payment->paymentStudent = $impotedStudent->id;
                                $payment->paymentRows = $fee['paymentRows'];
                                $payment->paymentAmount = $fee['paymentAmount'];
                                $payment->paymentDiscounted = $fee['paymentAmount'];
                                $payment->paymentDate = $fee['paymentDate'];
                                $payment->dueDate = $fee['dueDate'];
                                $payment->paymentUniqid = uniqid();
                                $payment->paymentStatus = 0;
                                $payment->fine_amount = $fee['fine_amount'];
                                $payment->save();
                            }
                        }
                    }
                // **********************

                $studentAcademicYears = new student_academic_years();
                $studentAcademicYears->studentId = $impotedStudent->id;
                $studentAcademicYears->academicYearId = $this->panelInit->selectAcYear;
                $studentAcademicYears->classId = $student['class'];
                $studentAcademicYears->sectionId = $student['section'];
                $studentAcademicYears->save();
                $completes++;
            }
            if( $completes > 0 )
            {
                return $this->panelInit->apiOutput( true, "Import Students", "$completes student were added successfully");
            } else return $this->panelInit->apiOutput( false, "Import Students", "all data you provided is not valid");
        }
        else
        {
            return $this->panelInit->apiOutput( false, "Import Students", "No data found to process");
        }
    }
}