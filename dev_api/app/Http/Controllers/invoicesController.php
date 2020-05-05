<?php
namespace App\Http\Controllers;

use App\Models2\MClass;
use App\Models2\Payment;
use App\Models2\PaymentsCollection;
use App\Models2\Section;
use App\Models2\User;
use Carbon\Carbon;
use Hashids\Hashids;
use App\Models2\StudentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class invoicesController extends Controller {

	var $data = array();
	var $panelInit;
	var $layout = 'dashboard';

	public function __construct(){
		if(app('request')->header('Authorization') != "" || \Input::has('token')){
			$this->middleware('jwt.auth');
		}else{
			$this->middleware('authApplication');
		}

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)){
			return \Redirect::to('/');
		}
	}

	public function preLoad()
	{
		$toReturn = array();
		$classes = MClass::select('id', 'className as name')->get()->toArray();
		$current_user = \Auth::user()->id;
		$user = \Auth::user();
		$role = $user->role;
		$upClasses = [];
		foreach( $classes as $index => $class )
		{
			$class_id = $class['id'];
			$sections = Section::select('id', 'sectionName as name')->where('classId', $class_id)->get()->toArray();
			$classes[$index]['sections'] = $sections;
			$upSections = [];
			foreach( $sections as $innerKey => $section )
			{
				$upSections[$innerKey] = [
					'id' => $section['id'],
					'name' => "( " . $class['name'] . " ) " . $section['name']
				];
			}
			$upClasses[$class_id] = [
				'id' => $class['id'],
				'name' => $class['name'],
				'sections' => $upSections
			];
		}
		$toReturn['classes'] = $classes;
		$toReturn['upClasses'] = $upClasses;
		$toReturn['payment_methods'] = Payment::payment_methods();
		$toReturn['types'] = StudentType::select('id','title as name') ->get()->toArray();
		return $toReturn;
	}

	public function listAll($page = 1)
	{

		if(!$this->panelInit->can( array("Invoices.list","Invoices.View","Invoices.addPayment","Invoices.editPayment","Invoices.delPayment","Invoices.collInvoice","Invoices.payRevert","Invoices.Export","Invoices.dueInvoices") )){
			exit;
		}

		$toReturn = array();
		// dd($this->data['users']->role);
		if($this->data['users']->role == "student" || $this->data['users']->role == "teacher"){
			$toReturn['invoices'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->where('paymentDate','<=',time())
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}elseif($this->data['users']->role == "parent"){
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				foreach($parentOf as $value){
					$studentId[] = $value['id'];
				}
			}
			// dd($studentId);
			$toReturn['invoices'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}else{
			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}

		if(\Input::has('searchInput')){
			$searchInput = \Input::get('searchInput');
			if(is_array($searchInput)){

				if(isset($searchInput['dueInv']) AND $searchInput['dueInv'] == true){
					$toReturn['invoices'] = $toReturn['invoices']->where('dueDate','<=',time())->where('paymentStatus','!=','1');
				}

				if(isset($searchInput['text']) AND strlen($searchInput['text']) > 0 ){
					$keyword = $searchInput['text'];
					$toReturn['invoices'] = $toReturn['invoices']->where(function($query) use ($keyword){
						$query->where('payments.paymentTitle','LIKE','%'.$keyword.'%');
						$query->orWhere('payments.id', $keyword);
						$query->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%');
						$query->orWhere('fullName','LIKE','%'.$keyword.'%');
						$query->orWhere('admission_number','LIKE','%'.$keyword.'%');
					});
				}

				if(isset($searchInput['paymentStatus']) AND $searchInput['paymentStatus'] != ""){
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentStatus',$searchInput['paymentStatus']);
				}

				if(isset($searchInput['fromDate']) AND $searchInput['fromDate'] != ""){
					$searchInput['fromDate'] = $this->panelInit->date_to_unix($searchInput['fromDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','>=',$searchInput['fromDate']);
				}

				if(isset($searchInput['toDate']) AND $searchInput['toDate'] != ""){
					$searchInput['toDate'] = $this->panelInit->date_to_unix($searchInput['toDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','<=',$searchInput['toDate']);
				}

			}
		}

		$toReturn['totalItems'] = $toReturn['invoices']->count();
		$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->take(all_pagination_number())->skip(all_pagination_number()* ($page - 1) )->get();

		foreach ($toReturn['invoices'] as $key => $value) {
			$toReturn['invoices'][$key]->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
			$dueforcompare = $toReturn['invoices'][$key]->dueDate ;
			$toReturn['invoices'][$key]->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);

			$toReturn['invoices'][$key]->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;

			$toReturn['invoices'][$key]->paymentDiscounted = $toReturn['invoices'][$key]->paymentDiscounted + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentDiscounted) /100;
			if($dueforcompare < time() ){
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentAmount + $toReturn['invoices'][$key]->fine_amount ;
			}else{
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentAmount;
				$toReturn['invoices'][$key]->fine_amount = '-' ;
			}
		}

		$toReturn['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

		$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		foreach ($classes as $class) {
			$toReturn['classes'][$class->id] = $class->className ;
		}

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'invoice', null);

		return $toReturn;
	}

	public function fiterFeeInvoices($page = 1)
	{
		User::$withoutAppends = true;
		if( !$this->panelInit->can( array("Invoices.list","Invoices.View") ) )
		{
			return $this->panelInit->apiOutput(false, 'Filter Invoices', "You don't have the permission to filter the invoices");
		}
		$toReturn = array();
		if( $this->data['users']->role == "student" || $this->data['users']->role == "teacher" )
		{
			$toReturn['invoices'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->where('paymentDate','<=',time())
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}
		elseif( $this->data['users']->role == "parent" )
		{
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if( is_array( $parentOf ) ) { foreach($parentOf as $value) { $studentId[] = $value['id']; } }
			$toReturn['invoices'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}
		else
		{
			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}
		$studentIds = [];
		if( \Input::has('class') )
		{
			if( \Input::get('class') != "" )
			{
				$class_id = \Input::get('class');
				$classIds[] = $class_id;
				$studentIds = User::getStudentsIdsFromClassesIds( $classIds );
			}
		}

		if( \Input::has('section') )
		{
			if( \Input::get('section') != "" && \Input::get('section') != "all" )
			{
				$section_id = \Input::get('section');
				$studentIds2 = User::getStudentIdsBySectionId( $section_id );
				if( count( $studentIds ) == 0 ) $studentIds = $studentIds2;
				else $studentIds = array_intersect( $studentIds, $studentIds2 );
			}
		}
		if( \Input::has('text') )
		{
			$keyword = trim( \Input::get('text') );
			if( $keyword )
			{
				$studentIds2 = User::getStudentIdsByTextQuery( $keyword );
				if( count( $studentIds ) == 0 ) $studentIds = $studentIds2;
				else $studentIds = array_intersect( $studentIds, $studentIds2 );
			}
		}
		$toReturn['invoices'] = $toReturn['invoices']->whereIn('paymentStudent', $studentIds);
		if( \Input::has('mode') )
		{
			if( \Input::get('mode') != "" && \Input::get('mode') != "All" )
			{
				$mode = \Input::get('mode');
				$toReturn['invoices'] = $toReturn['invoices']->where('paymentStatus', $mode);
			}
		}
		if( \Input::has('status') )
		{
			if( \Input::get('status') != "" && \Input::get('status') != "All" )
			{
				$paymentStatus = \Input::get('status');
				$toReturn['invoices'] = $toReturn['invoices']->where('paymentStatus', $paymentStatus);
			}
		}
		if( \Input::has('from') )
		{
			if( \Input::get('from') != "" )
			{
				$fromDate = $this->panelInit->date_to_unix( \Input::get('from') );
				$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate', '>=', $fromDate);
			}
		}
		if( \Input::has('to') )
		{
			if( \Input::get('to') != "" )
			{
				$toDate = $this->panelInit->date_to_unix( \Input::get('to') );
				$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate', '<=', $toDate);
			}
		}

		$toReturn['totalItems'] = $toReturn['invoices']->count();
		$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->take(all_pagination_number())->skip(all_pagination_number()* ($page - 1) )->get();
		$invoices = $toReturn['invoices'];
		foreach ($invoices as $key => $value) {
			$invoices[$key]->paymentDate = $this->panelInit->unix_to_date($invoices[$key]->paymentDate);
			$dueforcompare = $invoices[$key]->dueDate ;
			$invoices[$key]->dueDate = $this->panelInit->unix_to_date($invoices[$key]->dueDate);

			$invoices[$key]->paymentAmount = $invoices[$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$invoices[$key]->paymentAmount) /100;

			$invoices[$key]->paymentDiscounted = $invoices[$key]->paymentDiscounted + ($this->panelInit->settingsArray['paymentTax']*$invoices[$key]->paymentDiscounted) /100;
			if($dueforcompare < time() ){
				$invoices[$key]->paymentGross =  $invoices[$key]->paymentAmount + $invoices[$key]->fine_amount ;
			}else{
				$invoices[$key]->paymentGross =  $invoices[$key]->paymentAmount;
				$invoices[$key]->fine_amount = '-' ;
			}
		}
		foreach( $invoices as $key => $singleInvoice )
		{
			$user = User::find( $singleInvoice->studentId );
			// $invoices[$key]->paymentDate = date('Y/m/d', $singleInvoice->paymentDate);
			// $invoices[$key]->dueDate = date('Y/m/d', $singleInvoice->dueDate);
			if( $user )
			{
				if( $user->mclass ) $invoices[$key]->class = $user->mclass->className;
				else $invoices[$key]->class = "";
				if( $user->section ) $invoices[$key]->section = $user->section->sectionName;
				else $invoices[$key]->section = "";
				$invoices[$key]->admission = $user->admission_number;
			} else {
				$invoices[$key]->class = "";
				$invoices[$key]->section = "";
				$invoices[$key]->admission = "";
			}
			$invoices[$key]->amountToCollect = "";
			$invoices[$key]->dateOfCollect = "";
			$invoices[$key]->paymentMethod = "";
			$invoices[$key]->notesOfCollect = "";
		}
		$toReturn['invoices'] = $invoices;
		$toReturn['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

		$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		foreach ($classes as $class) { $toReturn['classes'][$class->id] = $class->className ; }

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'invoice', null);

		return $toReturn;
	}

	public function uploadedExcel()
	{
		if( !$this->panelInit->can("Invoices.addPayment") ) { return $this->panelInit->apiOutput(false, $this->panelInit->language['Import'], "You don't have permission to create invoice using Excel sheet"); }
		if( !\Input::hasFile('excelcsv') ) { return $this->panelInit->apiOutput(false, $this->panelInit->language['Import'], $this->panelInit->language['specifyFileToImport']); }
		// if( \Input::get('sections') == 'undefined' ) echo 'tamam'; else

		$student_types = []; $class_ids = []; $section_ids = [];
		if( \Input::get('student_types') != '' && \Input::get('student_types') != 'undefined' ) { $student_types = explode(',', \Input::get('student_types') ); }
		if( \Input::get('classes') != '' && \Input::get('classes') != 'undefined' ) { $class_ids = explode(',', \Input::get('classes') ); }
		if( \Input::get('sections') != '' && \Input::get('sections') != 'undefined' ) { $section_ids = explode(',', \Input::get('sections') ); }
		if(count($class_ids) == 0 || (count($class_ids) == 1 && count($section_ids) == 0)) {
			return $this->panelInit->apiOutput(false, 'Incomplete import fees', 'Fee Group Type, class or section can not be empty.');
		}
		// Get payment students ----------------------------------------------
		User::$withoutAppends = true;
		$students = User::where('role','student')->where('activated','1');
		if(count($class_ids) > 0) { $students = $students->whereIn('studentClass', $class_ids); }
		if(count($class_ids) == 1 && count($section_ids) > 0){ $students = $students->whereIn('studentSection', $section_ids); }
		if(count($student_types) > 0) { $students = $students->whereIn('studentType', $student_types); }
		$paymentStudent = $students->select('id')->get()->toArray();
		if(count($paymentStudent) <= 0) { return $this->panelInit->apiOutput(false, 'Incomplete import fees', 'There are no results for students according to your filter.'); }
		// end Get payment students ----------------------------------------------
		$temp_filename = $_FILES['excelcsv']['tmp_name'];
		$readExcel = \Excel::load($temp_filename, function($reader) {
			$reader->noHeading();
		})->toArray();

		$items = [];
		$previewItems = [];

		foreach ($readExcel as $i => $row) {
			$item = [];
			if(count(array_unique($row)) == 1 && array_unique($row)[0] == null) {
				continue;
			}
			if (strpos($row[0], 'Note:') !== false) {
			continue;
			}
			if(!is_null($readExcel[0][$i]) && $i > 0) {
				$item['fee_title'] = $readExcel[0][$i];
				for ($j = 1; $j < count($readExcel); $j++) {
					if (strpos($readExcel[$j][0], 'Note:') !== false) {
					continue;
					}
					if($readExcel[$j] != null) {
						$insert = $readExcel[$j][$i];
						$item[$readExcel[$j][0]] = $insert;
					}
				}
				$items[] = $item;
			}

			// preview import --------------
			foreach ($row as $el_key => $element) {
				if($el_key > 0 && is_null($element)) {
					unset($row[$el_key]);
				}
				if(substr($element, 0, 1) == '*') {
					$row[$el_key] = substr($element, 1);
				}
			}
			if($i == 1 || $i == 2) {
				foreach ($row as $el_key => $element) {
					if($el_key > 0 && !is_null($element)) {
						$row[$el_key] = $this->panelInit->unix_to_date($element->timestamp);
					}
				}
			}
			$previewItems[] = $row;
			// end preview import -----------
		}

		if( \Input::get('review_import') == 'false')
		{
			foreach ($items as $item)
			{
				foreach( $paymentStudent as $key => $value )
				{
					if( $value['id'] == "" || $value['id'] == "0") { continue; }
					$payments = new \payments();
					$payments->paymentTitle = $item['fee_title'];
					$payments->paymentStudent = $value['id'];

					// payment rows ------------
					$fees_list = [];
					foreach( $item as $fee => $fee_value )
					{
						if(substr($fee, 0, 1) == '*') { $fees_list[] = [ 'title' => substr($fee, 1), 'amount' => $fee_value ]; }
					}

					if(count($fees_list) <= 0) { return $this->panelInit->apiOutput(false, 'Incomplete import fees', 'Payment rows can not be empty.'); }

					$payments->paymentRows = json_encode($fees_list);
					$paymentAmount = 0;
					foreach($fees_list as $key => $value) { $paymentAmount += $value['amount']; }
					$payments->paymentAmount = $paymentAmount;
					$payments->paymentDiscounted = $paymentAmount;
					// -----------------

					$payments->paymentDate = $item['Fee start date']->timestamp;
					$payments->dueDate = $item['Fee due date']->timestamp;
					$payments->paymentUniqid = uniqid();
					$payments->paymentStatus = 0;
					$payments->fine_amount = $item['Late fee fine'];
					$payments->save();
				}
			}

			user_log('Payments', 'import_fees');

			return $this->panelInit->apiOutput(true, 'Import Fee status', 'Success import fees');
		}
		else
		{
			return response()->json([
				'preview_items' => true,
				'previewItems' => $previewItems
			]);
		}
	}

	public function createManualFee()
	{
		if( !$this->panelInit->can("Invoices.addPayment") ) { return $this->panelInit->apiOutput(false, $this->panelInit->language['Import'], "You don't have permission to create invoice using Excel sheet"); }
		$student_types = []; $class_ids = []; $section_ids = [];
		if( \Input::get('student_types') != '' && \Input::get('student_types') != 'undefined' ) { $student_types = \Input::get('student_types'); }
		if( \Input::get('classes') != '' && \Input::get('classes') != 'undefined' ) { $class_ids = \Input::get('classes'); }
		if( \Input::get('sections') != '' && \Input::get('sections') != 'undefined' ) { $section_ids = \Input::get('sections'); }
		if(count($class_ids) == 0 || (count($class_ids) == 1 && count($section_ids) == 0)) {
			return $this->panelInit->apiOutput(false, 'Creating new invoice', 'Fee Group Type, class or section can not be empty.');
		}
		User::$withoutAppends = true;
		$students = User::where('role','student')->where('activated','1');
		if(count($class_ids) > 0) { $students = $students->whereIn('studentClass', $class_ids); }
		if(count($class_ids) == 1 && count($section_ids) > 0){ $students = $students->whereIn('studentSection', $section_ids); }
		if(count($student_types) > 0) { $students = $students->whereIn('studentType', $student_types); }
		$paymentStudent = $students->select('id', 'fullName as name')->get()->toArray();
		if( !\Input::has('feeTitle') || \Input::get('feeTitle') == "" ) { return $this->panelInit->apiOutput( false, "Creating new invoice", "Fee Title is missing" ); }
		if( !\Input::has('feeDate') || \Input::get('feeDate') == "") { return $this->panelInit->apiOutput( false, "Creating new invoice", "Date is missing" ); }
		if( !\Input::has('dueDate') || \Input::get('dueDate') == "") { return $this->panelInit->apiOutput( false, "Creating new invoice", "Due Date is missing" ); }
		if( !\Input::has('details') ) { return $this->panelInit->apiOutput( false, "Creating new invoice", "Fee details is missing" ); }
		if( !\Input::has('amount') || \Input::get('amount') == 0 || \Input::get('amount') == '' ) { return $this->panelInit->apiOutput( false, "Creating new invoice", "Total amount is missing" ); }
		$date = formatDate( \Input::get('feeDate') );
        if( !$date ) { return $this->panelInit->apiOutput( false, "Creating new invoice", "Date has invalid format" ); }
        if( !toUnixStamp( $date ) ) { return $this->panelInit->apiOutput( false, "Creating new invoice", "Date has invalid format" ); }
        $dueDate = formatDate( \Input::get('dueDate') );
        if( !$dueDate ) { return $this->panelInit->apiOutput( false, "Creating new invoice", "Due Date has invalid format" ); }
		if( !toUnixStamp( $dueDate ) ) { return $this->panelInit->apiOutput( false, "Creating new invoice", "Due Date has invalid format" ); }
		$details = \Input::get('details');
		$paymentRows = []; $index = 0;
		$paymentAmount = \Input::get('amount');
		foreach( $details as $detail )
		{
			// $paymentRows[$index] = [ 'title' => $detail['title'], 'amount' => (string)$paymentAmount ];
			$paymentRows[$index] = [ 'title' => $detail['title'], 'amount' => (string)$detail['amount'] ];
			$index++;
		}
		if(count($paymentStudent) <= 0) { return $this->panelInit->apiOutput(false, 'Creating new invoice', 'There are no results for students according to your filter.'); }
		foreach( $paymentStudent as $member )
		{
			$member_id = $member['id']; $name = $member['name'];
			if( $member_id == "" || $member_id == "0" || $member_id == 0 || $member_id == NULL) { continue; }
			$payments = new \payments();
			$payments->paymentTitle = \Input::get('feeTitle');
			if( \Input::has('desc') ) { $payments->paymentDescription = \Input::get('desc'); }
			$payments->paymentStudent = $member_id;
			$payments->paymentAmount = $paymentAmount;
			$payments->paymentDiscounted = $paymentAmount;
			$payments->paymentDate = toUnixStamp( $date );
			$payments->dueDate = toUnixStamp( $dueDate );
			$payments->paymentUniqid = uniqid();
			$payments->paymentStatus = 0;
			$payments->paymentRows = json_encode( $paymentRows );
			$payments->fine_amount = \Input::has('fine') ? \Input::get('fine') : 0;
			$payments->save();
			user_log('Payments', 'create', 'Transportation Fee for: ' . $name);
		}
		return $this->panelInit->apiOutput( true, "Creating new invoice", "Fees Invoices created successfully" );
	}

	public function listBulkFees() {
		// check valid inputs --------------------
			$student_type = 0;
			$class_ids = [];
			$section_ids = [];
			if(!is_null(request()->input('student_type'))) {
				$student_type = request()->input('student_type');
			}
			if(!is_null(request()->input('classes'))) {
				$class_ids = request()->input('classes');
			}
			if(!is_null(request()->input('sections'))) {
				$section_ids = request()->input('sections');
			}

			if(count($class_ids) == 0 || (count($class_ids) == 1 && count($section_ids) == 0)) {
				return $this->panelInit->apiOutput(false, 'Incomplete view bulk fees', 'Fee Group Type, class or section can not be empty.');
			}
		// end check -----------------------------

		// Get payment students ----------------------------------------------
			User::$withoutAppends = true;
			$students = User::where('role','student')->where('activated','1');
			if(count($class_ids) > 0){
				$students = $students->whereIn('studentClass', $class_ids);
			}
			if(count($class_ids) == 1 && count($section_ids) > 0){
				$students = $students->whereIn('studentSection', $section_ids);
			}
			if($student_type != 0){
				$students = $students->where('studentType', $student_type);
			}
			$paymentStudent = $students->distinct()->pluck('id')->toArray();

			if(count($paymentStudent) <= 0) {
				return $this->panelInit->apiOutput(false, 'Incomplete view bulk fees', 'There are no results for students according to your filter.');
			}
		// end Get payment students -------------------------------------------

		$bulk_fees = Payment::whereIn('paymentStudent', $paymentStudent)
			->where('paymentRows', '!=', '[]')
			->groupBy('paymentTitle', 'paymentDate', 'dueDate')
			->select('paymentTitle', 'paymentAmount', 'paymentRows', 'paymentDate', 'dueDate', 'fine_amount')
			->get()
			->toArray();

		if(count($bulk_fees) <= 0) {
			return $this->panelInit->apiOutput(false, 'Incomplete view bulk fees', 'There are no results for payments related to your filter.');
		}

		$bulk_fees_titles = [];
		$bulk_fees_paymentRows = [];
		foreach ($bulk_fees as $key => $fee) {
			$bulk_fees[$key]['paymentDate'] = $this->panelInit->unix_to_date($fee['paymentDate']);
			$bulk_fees[$key]['dueDate'] = $this->panelInit->unix_to_date($fee['dueDate']);
			$bulk_fees[$key]['paymentRows'] = json_decode($fee['paymentRows']);
			$bulk_fees_titles[] = $fee['paymentTitle'];

			foreach (json_decode($fee['paymentRows']) as $row) {
				if(!in_array(strtolower($row->title), $bulk_fees_paymentRows)) {
					$bulk_fees_paymentRows[] = strtolower($row->title);
				}
			}
		}

		return response()->json([
			'bulk_fees_titles' => $bulk_fees_titles,
			'bulk_fees' => $bulk_fees,
			'student_ids' => $paymentStudent,
			'bulk_fees_paymentRows' => $bulk_fees_paymentRows,
		]);
	}

	public function updateBulkFees() {
		$bulk_fees = request()->input('form');
		$current_student_ids = request()->input('student_ids');

		if(is_null($current_student_ids) || !is_array($current_student_ids) || count($current_student_ids) <= 0) {
			return $this->panelInit->apiOutput(false, 'Invalid update bulk fees', 'Selected students is not correct.');
		}

		if(is_array($bulk_fees)) {
			foreach ($bulk_fees as $fee) {
				$paymentRows = [];
				foreach ($fee['paymentRows'] as $row) {
					if(!is_null($row['amount']) && $row['amount'] > 0) {
						$paymentRows[] = $row;
					}
				}
				$totalAmount = 0;
				foreach ($paymentRows as $row) {
					$totalAmount += $row['amount'];
				}
				$paymentRows = json_encode($paymentRows);
				Payment::where([
					'paymentTitle' => $fee['paymentTitle'],
					'paymentAmount' => $fee['paymentAmount'],
				])
				->whereIn('paymentStudent', $current_student_ids)
				->update([
					'paymentDate' => $this->panelInit->date_to_unix($fee['paymentDate']),
					'dueDate' => $this->panelInit->date_to_unix($fee['dueDate']),
					'fine_amount' => $fee['fine_amount'],
					'paymentRows' => $paymentRows,
					'paymentAmount' => $totalAmount,
					'paymentDiscounted' => $totalAmount,
				]);
			}

			return $this->panelInit->apiOutput(true, 'Success update bulk fees');
		}
	}

	public function listAllNotPaid($page = 1) {
		if(!$this->panelInit->can( array("Invoices.list","Invoices.View","Invoices.addPayment","Invoices.editPayment","Invoices.delPayment","Invoices.collInvoice","Invoices.payRevert","Invoices.Export","Invoices.dueInvoices") )){
			exit;
		}

		$toReturn = array();
		// dd($this->data['users']->role);
		if($this->data['users']->role == "student" || $this->data['users']->role == "teacher"){
			$toReturn['invoices'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->where('paymentStatus', '!=', 1)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}elseif($this->data['users']->role == "parent"){
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				foreach($parentOf as $value){
					$studentId[] = $value['id'];
				}
			}
			$toReturn['invoices'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->where('paymentStatus', '!=', 1)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}else{
			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->where('paymentStatus', '!=', 1)
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}

		if(\Input::has('searchInput')){
			$searchInput = \Input::get('searchInput');
			if(is_array($searchInput)){

				if(isset($searchInput['dueInv']) AND $searchInput['dueInv'] == true){
					$toReturn['invoices'] = $toReturn['invoices']->where('dueDate','<=',time())->where('paymentStatus','!=','1');
				}

				if(isset($searchInput['text']) AND strlen($searchInput['text']) > 0 ){
					$keyword = $searchInput['text'];
					$toReturn['invoices'] = $toReturn['invoices']->where(function($query) use ($keyword){
						$query->where('payments.paymentTitle','LIKE','%'.$keyword.'%');
						$query->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%');
						$query->orWhere('fullName','LIKE','%'.$keyword.'%');
						$query->orWhere('admission_number','LIKE','%'.$keyword.'%');
					});
				}

				if(isset($searchInput['paymentStatus']) AND $searchInput['paymentStatus'] != ""){
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentStatus',$searchInput['paymentStatus']);
				}

				if(isset($searchInput['fromDate']) AND $searchInput['fromDate'] != ""){
					$searchInput['fromDate'] = $this->panelInit->date_to_unix($searchInput['fromDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','>=',$searchInput['fromDate']);
				}

				if(isset($searchInput['toDate']) AND $searchInput['toDate'] != ""){
					$searchInput['toDate'] = $this->panelInit->date_to_unix($searchInput['toDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','<=',$searchInput['toDate']);
				}

			}
		}

		$toReturn['totalItems'] = $toReturn['invoices']->count();
		$toReturn['invoices'] = $toReturn['invoices']
			->orderBy('paymentDate', 'DESC')
			->take(all_pagination_number())
			->skip(all_pagination_number()* ($page - 1) )
			->get();

		foreach ($toReturn['invoices'] as $key => $value) {
			$toReturn['invoices'][$key]->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
			$dueforcompare = $toReturn['invoices'][$key]->dueDate ;
			$toReturn['invoices'][$key]->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);

			$toReturn['invoices'][$key]->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;

			$toReturn['invoices'][$key]->paymentDiscounted = $toReturn['invoices'][$key]->paymentDiscounted + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentDiscounted) /100;
			if($dueforcompare < time() ){
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentAmount + $toReturn['invoices'][$key]->fine_amount ;
			}else{
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentAmount;
				$toReturn['invoices'][$key]->fine_amount = '-' ;
			}
		}

		$toReturn['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

		$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		foreach ($classes as $class) {
			$toReturn['classes'][$class->id] = $class->className ;
		}

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'invoice', null);

		return $toReturn;
	}

	public function advancedListAll($page = 1)
	{
		if(!$this->panelInit->can( array("Invoices.list","Invoices.View","Invoices.addPayment","Invoices.editPayment","Invoices.delPayment","Invoices.collInvoice","Invoices.payRevert","Invoices.Export","Invoices.dueInvoices") )){
			exit;
		}

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'invoice', null);

		$toReturn = array();

		if($this->data['users']->role == "student" || $this->data['users']->role == "teacher"){
			$toReturn['invoices'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->where('paymentDate','>',time())
						->where('dueDate','>',time())
						->where('paymentStatus', '!=', 1)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}elseif($this->data['users']->role == "parent"){
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				foreach($parentOf as $value){
					$studentId[] = $value['id'];
				}
			}
			$toReturn['invoices'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->where('paymentDate','>',time())
						->where('dueDate','>',time())
						->where('paymentStatus', '!=', 1)
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}else{
			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->where('paymentDate','>',time())
						->where('dueDate','>',time())
						->where('paymentStatus', '!=', 1)
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}

		if(\Input::has('searchInput')){
			$searchInput = \Input::get('searchInput');
			if(is_array($searchInput)){
				if(isset($searchInput['text']) AND strlen($searchInput['text']) > 0 ){
					$keyword = $searchInput['text'];
					$toReturn['invoices'] = $toReturn['invoices']->where(function($query) use ($keyword){
						$query->where('payments.paymentTitle','LIKE','%'.$keyword.'%');
						$query->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%');
						$query->orWhere('fullName','LIKE','%'.$keyword.'%');
						$query->orWhere('admission_number','LIKE','%'.$keyword.'%');
					});
				}

				if(isset($searchInput['paymentStatus']) AND $searchInput['paymentStatus'] != ""){
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentStatus',$searchInput['paymentStatus']);
				}

				if(isset($searchInput['fromDate']) AND $searchInput['fromDate'] != ""){
					$searchInput['fromDate'] = $this->panelInit->date_to_unix($searchInput['fromDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','>=',$searchInput['fromDate']);
				}

				if(isset($searchInput['toDate']) AND $searchInput['toDate'] != ""){
					$searchInput['toDate'] = $this->panelInit->date_to_unix($searchInput['toDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','<=',$searchInput['toDate']);
				}

			}
		}

		$toReturn['totalItems'] = $toReturn['invoices']->count();
		$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->take(all_pagination_number())->skip(all_pagination_number()* ($page - 1) )->get();

		foreach ($toReturn['invoices'] as $key => $value) {
			$toReturn['invoices'][$key]->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
			$dueforcompare = $toReturn['invoices'][$key]->dueDate ;
			$toReturn['invoices'][$key]->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);

			$toReturn['invoices'][$key]->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;

			$toReturn['invoices'][$key]->paymentDiscounted = $toReturn['invoices'][$key]->paymentDiscounted + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentDiscounted) /100;
			if($dueforcompare < time() ){
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentDiscounted + $toReturn['invoices'][$key]->fine_amount ;
			}else{
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentDiscounted ;
				$toReturn['invoices'][$key]->fine_amount = '-' ;
			}
		}

		$toReturn['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

		$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		foreach ($classes as $class) {
			$toReturn['classes'][$class->id] = $class->className ;
		}

		return $toReturn;
	}

	public function currentListAll($page = 1)
	{
		if(!$this->panelInit->can( array("Invoices.list","Invoices.View","Invoices.addPayment","Invoices.editPayment","Invoices.delPayment","Invoices.collInvoice","Invoices.payRevert","Invoices.Export","Invoices.dueInvoices") )){
			exit;
		}

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'invoice', null);

		$toReturn = array();

		if($this->data['users']->role == "student" || $this->data['users']->role == "teacher"){
			$toReturn['invoices'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->where('paymentDate','<=',time())
						->where('paymentStatus', '!=', 1)
						->where('dueDate','>',time())
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}elseif($this->data['users']->role == "parent"){
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				foreach($parentOf as $value){
					$studentId[] = $value['id'];
				}
			}
			$toReturn['invoices'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->where('paymentDate','<=',time())
						->where('dueDate','>',time())
						->where('paymentStatus', '!=', 1)
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}else{
			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->where('paymentDate','<=',time())
						->where('dueDate','>',time())
						->where('paymentStatus', '!=', 1)
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}

		if(\Input::has('searchInput')){
			$searchInput = \Input::get('searchInput');
			if(is_array($searchInput)){
				if(isset($searchInput['text']) AND strlen($searchInput['text']) > 0 ){
					$keyword = $searchInput['text'];
					$toReturn['invoices'] = $toReturn['invoices']->where(function($query) use ($keyword){
						$query->where('payments.paymentTitle','LIKE','%'.$keyword.'%');
						$query->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%');
						$query->orWhere('fullName','LIKE','%'.$keyword.'%');
						$query->orWhere('admission_number','LIKE','%'.$keyword.'%');
					});
				}

				if(isset($searchInput['paymentStatus']) AND $searchInput['paymentStatus'] != ""){
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentStatus',$searchInput['paymentStatus']);
				}

				if(isset($searchInput['fromDate']) AND $searchInput['fromDate'] != ""){
					$searchInput['fromDate'] = $this->panelInit->date_to_unix($searchInput['fromDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','>=',$searchInput['fromDate']);
				}

				if(isset($searchInput['toDate']) AND $searchInput['toDate'] != ""){
					$searchInput['toDate'] = $this->panelInit->date_to_unix($searchInput['toDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','<=',$searchInput['toDate']);
				}

			}
		}

		$toReturn['totalItems'] = $toReturn['invoices']->count();
		$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->take(all_pagination_number())->skip(all_pagination_number()* ($page - 1) )->get();

		foreach ($toReturn['invoices'] as $key => $value) {
			$toReturn['invoices'][$key]->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
			$dueforcompare = $toReturn['invoices'][$key]->dueDate ;
			$toReturn['invoices'][$key]->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);

			$toReturn['invoices'][$key]->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;

			$toReturn['invoices'][$key]->paymentDiscounted = $toReturn['invoices'][$key]->paymentDiscounted + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentDiscounted) /100;
			if($dueforcompare < time() ){
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentDiscounted + $toReturn['invoices'][$key]->fine_amount ;
			}else{
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentDiscounted ;
				$toReturn['invoices'][$key]->fine_amount = '-' ;
			}
		}

		$toReturn['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

		$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		foreach ($classes as $class) {
			$toReturn['classes'][$class->id] = $class->className ;
		}

		return $toReturn;
	}

	public function listPaid($page = 1)
	{
		if(!$this->panelInit->can( array("Invoices.list","Invoices.View","Invoices.addPayment","Invoices.editPayment","Invoices.delPayment","Invoices.collInvoice","Invoices.payRevert","Invoices.Export","Invoices.dueInvoices") )){
			exit;
		}

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'invoice', null);

		$toReturn = array();

		if($this->data['users']->role == "student" || $this->data['users']->role == "teacher"){

			$toReturn['invoices'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->where('paymentStatus', 1)
						->where('where','<=',time())
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');

		}elseif($this->data['users']->role == "parent"){

			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				foreach($parentOf as $value){
					$studentId[] = $value['id'];
				}
			}
			$toReturn['invoices'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->where('paymentStatus', 1)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');
		}else{

			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->where('paymentStatus', 1)
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paidAmount as paidAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paymentDiscounted as paymentDiscounted',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName');

		}

		// Search section
		if(\Input::has('searchInput')){
			$searchInput = \Input::get('searchInput');
			if(is_array($searchInput)){

				if(isset($searchInput['dueInv']) AND $searchInput['dueInv'] == true){
					$toReturn['invoices'] = $toReturn['invoices']
					  ->where('dueDate','<',time())->where('paymentStatus','!=','1');
				}

				if(isset($searchInput['text']) AND strlen($searchInput['text']) > 0 ){
					$keyword = $searchInput['text'];
					$toReturn['invoices'] = $toReturn['invoices']->where(function($query) use ($keyword){
						$query->where('payments.paymentTitle','LIKE','%'.$keyword.'%');
						$query->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%');
						$query->orWhere('fullName','LIKE','%'.$keyword.'%');
						$query->orWhere('admission_number','LIKE','%'.$keyword.'%');
					});
				}

				if(isset($searchInput['paymentStatus']) AND $searchInput['paymentStatus'] != ""){
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentStatus',$searchInput['paymentStatus']);
				}

				if(isset($searchInput['fromDate']) AND $searchInput['fromDate'] != ""){
					$searchInput['fromDate'] = $this->panelInit->date_to_unix($searchInput['fromDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','>=',$searchInput['fromDate']);
				}

				if(isset($searchInput['toDate']) AND $searchInput['toDate'] != ""){
					$searchInput['toDate'] = $this->panelInit->date_to_unix($searchInput['toDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','<=',$searchInput['toDate']);
				}

			}
		}

		$toReturn['totalItems'] = $toReturn['invoices']->count();
		$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->take(all_pagination_number())->skip(all_pagination_number()* ($page - 1) )->get();

		foreach ($toReturn['invoices'] as $key => $value) {
			$toReturn['invoices'][$key]->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
			$dueforcompare = $toReturn['invoices'][$key]->dueDate ;
			$toReturn['invoices'][$key]->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);

			$toReturn['invoices'][$key]->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;

			$toReturn['invoices'][$key]->paymentDiscounted = $toReturn['invoices'][$key]->paymentDiscounted + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentDiscounted) /100;
			if($dueforcompare < time() ){
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentDiscounted + $toReturn['invoices'][$key]->fine_amount ;
			}else{
				$toReturn['invoices'][$key]->paymentGross =  $toReturn['invoices'][$key]->paymentDiscounted ;
				$toReturn['invoices'][$key]->fine_amount = '-' ;
			}
		}

		$toReturn['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

		$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		foreach ($classes as $class) {
			$toReturn['classes'][$class->id] = $class->className ;
		}

		return $toReturn;
	}

	public function delete($id){

		if(!$this->panelInit->can( "Invoices.delPayment" )){
			exit;
		}

		if ( $postDelete = \payments::where('id', $id)->first() ) {
				user_log('Payments', 'delete', '#'. $postDelete->id);
        $postDelete->delete();
        return $this->panelInit->apiOutput(true,$this->panelInit->language['delPayment'],$this->panelInit->language['paymentDel']);
    }else{
        return $this->panelInit->apiOutput(false,$this->panelInit->language['delPayment'],$this->panelInit->language['paymentNotExist']);
    }
	}

	public function create(){
		if(!$this->panelInit->can( "Invoices.addPayment" )){
			exit;
		}

		$craetedPayments = array();
		// $paymentStudent = \Input::get('paymentStudent');
		// if(!is_array($paymentStudent)){
		// 	return $this->panelInit->apiOutput(false,$this->panelInit->language['addPayment'],"No students are selected");
		// }

		$feeFor = \Input::get('feeFor');
		$feeAllocationfeeForInfo = '';
		if(\Input::get('feeFor') == "class"){
			$feeForInfo = array();

			if(\Input::has('feeSchDetailsClass')){
				$feeForInfo['class'] = \Input::get('feeSchDetailsClass');
			}

			if(\Input::has('feeSchDetailsClassSection')){
				$feeForInfo['section'] = json_encode(\Input::get('feeSchDetailsClassSection'));
			}

			if(\Input::has('feeSchDetailsStudentType')){
				$feeForInfo['student_type'] = \Input::get('feeSchDetailsStudentType');
			}

			$feeAllocationfeeForInfo = json_encode($feeForInfo);
			$paymentStudent = $this->panelInit->getPaymentUsers($feeFor,$feeAllocationfeeForInfo);
		}elseif(\Input::get('feeFor') == "student" && \Input::has('paymentStudent')){
			$paymentStudent = \Input::get('paymentStudent');
		}

		$tokens_list = array();
		$user_ids = array();
		$invoice_ids = array();
		foreach($paymentStudent as $key => $value){
			if($value['id'] == "" || $value['id'] == "0"){
				continue;
			}
			$payments = new \payments();
			$payments->paymentTitle = \Input::get('paymentTitle');
			if(\Input::has('paymentDescription')){
				$payments->paymentDescription = \Input::get('paymentDescription');
			}
			$payments->paymentStudent = $value['id'];

			if(\Input::has('paymentRows')){
				$payments->paymentRows = json_encode(\Input::get('paymentRows'));

				$paymentAmount = 0;
				$paymentRows = \Input::get('paymentRows');
				foreach($paymentRows as $key => $value){
					$paymentAmount += $value['amount'];
				}
			}else{
				$paymentRows = array();
				$payments->paymentRows = json_encode($paymentRows);
				$paymentAmount = 0;
			}

			$payments->paymentAmount = $paymentAmount;
			$payments->paymentDiscounted = $paymentAmount;
			$payments->paymentDate = $this->panelInit->date_to_unix(\Input::get('paymentDate'));
			$payments->dueDate = $this->panelInit->date_to_unix(\Input::get('dueDate'));

			$payments->paymentUniqid = uniqid();
			$payments->paymentStatus = \Input::get('paymentStatus');
			$payments->fine_amount = \Input::get('fine_amount');
			if(\Input::get('paymentStatus') == 1){
				$payments->paidAmount = $paymentAmount;
				if(\Input::has('paidMethod')){
					$payments->paidMethod = \Input::get('paidMethod');
				}
				if(\Input::has('paidTime')){
					$payments->paidTime = $this->panelInit->date_to_unix(\Input::get('paidTime'));
				}
			}
			$payments->save();
			if($payments->paymentStatus == 0){
				$invoice_ids[] = $payments->id;
			}

			//Send Push Notifications
			// $user_list = \User::where('id', $payments->paymentStudent)->select('id', 'firebase_token')->get();
			// $student_id = array();
			// foreach($user_list as $ite){
			// 	$student_id[]="%\"id\":".$ite->id."}%" ;
			// }
			// $user_list_parents = array();
			// foreach($student_id as $itp){
			// 	$res = \User::where('role','parent')
			// 	  ->where('parentOf','like',$itp)
			// 	  ->select('id', 'firebase_token')->first();
			// 	if($res){
			// 		$user_list_parents[] = $res;
			// 	}
			// }

			// foreach ($user_list_parents as $value) {
			// 	if($value['firebase_token'] != ""){
			// 		if(is_array(json_decode($value['firebase_token']))) {
			// 			foreach (json_decode($value['firebase_token']) as $token) {
			// 				$tokens_list[] = $token;
			// 			}
			// 		} else if ($this->isJson($value['firebase_token'])) {
			// 			foreach (json_decode($value['firebase_token']) as $token) {
			// 				$tokens_list[] = $token;
			// 			}
			// 		} else {
			// 			$tokens_list[] = $value['firebase_token'];
			// 		}
			// 	}
			// 	$user_ids[] = $value['id'];
			// }

			// // for don't repeat for every student
			// $tokens_list = array_unique($tokens_list);

			// if(count($tokens_list) > 0){
			// 	$this->panelInit->send_push_notification(
			// 		$tokens_list,
			// 		$user_ids,
			// 		$this->panelInit->language['newPaymentNotif'],
			// 		$this->panelInit->language['Invoices'],
			// 		"invoice",$payments->id
			// 	);
			// } else {
			// 	$this->panelInit->save_notifications_toDB(
			// 		$tokens_list,
			// 		$user_ids,
			// 		$this->panelInit->language['newPaymentNotif'],
			// 		$this->panelInit->language['Invoices'],
			// 		"invoice",$payments->id
			// 	);
			// }

			$payments->paymentDate = \Input::get('paymentDate');
			$payments->dueDate = \Input::get('dueDate');

			$craetedPayments[] = $payments->toArray();
		}

		user_log('Payments', 'create', 'Fee for: ' . $feeFor);

		// if(count($invoice_ids) > 0){
		// 	$this->calculate_discounts($invoice_ids);
		// }

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addPayment'],$this->panelInit->language['paymentCreated'],$craetedPayments );
	}

	protected function isJson($string) {
    $decoded = json_decode($string); // decode our JSON string
	    if ( !is_object($decoded) && !is_array($decoded) ) {
	        return false;
	    }
	    return (json_last_error() == JSON_ERROR_NONE);
	}

	function invoice($id){

		if(!$this->panelInit->can( array("Invoices.View","Invoices.editPayment","Invoices.collInvoice","Invoices.payRevert") )){
			exit;
		}

		$return = array();
		$return['payment'] = \payments::where('id',$id)->first()->toArray();
		$return['payment']['paymentDate'] = $this->panelInit->unix_to_date($return['payment']['paymentDate']);
		if($return['payment']['dueDate'] < time()){
			$return['payment']['isDueDate'] = true;

		}else{
			$return['payment']['isDueDate'] = false;
		}
		$return['payment']['dueDate'] = $this->panelInit->unix_to_date($return['payment']['dueDate']);
		if($return['payment']['paymentStatus'] == "1"){
			$return['payment']['paidTime'] = $this->panelInit->unix_to_date($return['payment']['paidTime']);
		}
		$return['payment']['paymentRows'] = json_decode($return['payment']['paymentRows'],true);
		$return['siteTitle'] = $this->panelInit->settingsArray['siteTitle'];
		$return['baseUrl'] = \URL::to('/');
		$return['address'] = $this->panelInit->settingsArray['address'];
		$return['address2'] = $this->panelInit->settingsArray['address2'];
		$return['systemEmail'] = $this->panelInit->settingsArray['systemEmail'];
		$return['phoneNo'] = $this->panelInit->settingsArray['phoneNo'];
		$return['paypalPayment'] = $this->panelInit->settingsArray['paypalPayment'];
		$return['currency_code'] = $this->panelInit->settingsArray['currency_code'];
		$return['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];
		$return['paymentTax'] = $this->panelInit->settingsArray['paymentTax'];
		$return['amountTax'] = ($this->panelInit->settingsArray['paymentTax']*$return['payment']['paymentAmount']) /100;
		$return['totalWithTax'] = $return['payment']['paymentAmount'] + $return['amountTax'];
		$return['pendingAmount'] = $return['totalWithTax'] - $return['payment']['paidAmount'];
		$return['user'] = \User::where('users.id',$return['payment']['paymentStudent'])
		  ->leftJoin('classes','users.studentClass','=','classes.id')
		  ->leftJoin('sections','users.studentSection','=','sections.id')
		  ->select('users.*','classes.className','sections.sectionName')
		  ->first()->toArray();

		$student_id = $return['user']['id'];
		$parentInfo = [];
		$parents = \User::where('role','parent')->select('id','parentOf','fullName','phoneNo')->where('parentOf','LIKE','%"id":'.$student_id.'%')->get()->toArray();
		foreach ($parents as $key => $value) {
			$value['parentOf'] = json_decode($value['parentOf'],true);
			foreach ($value['parentOf'] as $key_ => $value_) {
				if($value_['id'] == $student_id){
					$parentInfo[] = array(
						"parent"=>$value['fullName'],"relation"=>$value_['relation'],"id"=>$value['id'],
						"phoneNo"=>$value['phoneNo']
					);
				}
			}
		}
		$return['parentInfo'] = $parentInfo;

		$return['paypalEnabled'] = $this->panelInit->settingsArray['paypalEnabled'];
		$return['2coEnabled'] = $this->panelInit->settingsArray['2coEnabled'];
		$return['payumoneyEnabled'] = $this->panelInit->settingsArray['payumoneyEnabled'];
		$return['eazypayEnabled'] = $this->panelInit->settingsArray['eazypayEnabled'];

		$return['collection'] = \paymentsCollection::where('invoiceId',$id)->get()->toArray();
		foreach($return['collection'] as $key => $value){
			$return['collection'][$key]['collectionDate'] = $this->panelInit->unix_to_date($return['collection'][$key]['collectionDate']);
		}

		//get if any discount added to invoice
		$return['prices'] = $this->get_prices($return['payment']);

		if( $return['prices']['discount'] != 0 ){
			$return['payment']['paymentRows'][] = array("title"=>"Discount","amount"=>$return['prices']['discount']);
		}

		$return['payment']['paymentAmount'] = $return['prices']['original_discounted'] ;
		$return['totalWithTax'] = $return['prices']['total_with_tax'];

		if($return['payment']['isDueDate'] == true){
			$return['prices']['total_pending'] = $return['pendingAmount'] + $return['payment']['fine_amount'] ;
			$return['prices']['fine_amount'] = $return['payment']['fine_amount'];
		} else {
			$return['prices']['total_pending'] = $return['pendingAmount'];
			$return['prices']['fine_amount'] = 0;
		}
		$return['pendingAmount'] = $return['prices']['total_pending'];

		return $return;
	}

	public function calculate_discounts($invoices_list){
		$available_discount = \fee_discount::where('discount_status','1')->get();
		$section_enabeld = $this->panelInit->settingsArray['enableSections'];
		$userIds = array();

		$invoices = new \payments();
		if(is_array($invoices_list)){
			$invoices = $invoices->whereIn('id',$invoices_list);
		}else{
			$invoices = $invoices->where('id',$invoices_list);
		}
		$invoices = $invoices->get();
		foreach ($invoices as $key => $invoice) {
			$userIds[] = $invoice->paymentStudent;
		}

		//get users list
		$users = array();
		$users_list = \User::whereIn('id',$userIds)->select('studentClass','studentSection','id')->get()->toArray();
		foreach ($users_list as $key => $value) {
			$users[ $value['id'] ] = $value;
		}

		reset($invoices);
		foreach ($invoices as $key => $invoice) {
			reset($available_discount);

			$can_use_list = array();

			foreach ($available_discount as $key => $discount) {
				if($section_enabeld == true && isset($users[ $invoice->paymentStudent ]) ){
					if (strpos($discount->discount_assignment, 'cl-'.$users[ $invoice->paymentStudent ]['studentClass']."-".$users[ $invoice->paymentStudent ]['studentSection']) !== false) {
						$can_use_list[ $discount->id ] = $discount;
					}
				}
				if($section_enabeld == false && isset($users[ $invoice->paymentStudent ])){
					if (strpos($discount->discount_assignment, 'cl-'.$users[ $invoice->paymentStudent ]['studentClass']) !== false) {
						$can_use_list[ $discount->id ] = $discount;
					}
				}

				if (strpos($discount->discount_assignment, 'inv-'.$invoice->id) !== false) {
					$can_use_list[ $discount->id ] = $discount;
				}

				if (strpos($discount->discount_assignment, 'std-'.$invoice->paymentStudent) !== false) {
					$can_use_list[ $discount->id ] = $discount;
				}
			}

			$apply_discount = array();

			//Get max one
			if(count($can_use_list) >= 1){
				$fee_discount = array();

				foreach ($can_use_list as $key => $can_use_one) {
					$tmp_discount_calculation = $this->calculate_discount_value($can_use_one, $invoice->paymentAmount);
					if(count($fee_discount) == 0){
						$fee_discount = $tmp_discount_calculation;
					}else{
						if($tmp_discount_calculation['discount_value'] > $fee_discount['discount_value']){
							$fee_discount = $tmp_discount_calculation;
						}
					}
				}

			}

			if(count($can_use_list) == 0){
				\payments::where('id',$invoice->id)->update( array('paymentDiscount'=>0,'paymentDiscounted'=>$invoice->paymentAmount,'discount_id'=>0) );
			}else{
				if($fee_discount['discount_value'] > $invoice->paymentDiscount){
					$paymentDiscounted = $invoice->paymentAmount - $fee_discount['discount_value'];
					$update_invoice = array('paymentDiscount'=>$fee_discount['discount_value'],'paymentDiscounted'=>$paymentDiscounted,'discount_id'=>$fee_discount['discount_id']);
					if($paymentDiscounted == 0){
						$update_invoice['paymentStatus'] = 1;
					}
					\payments::where('id',$invoice->id)->update( $update_invoice );
				}
			}

		}
	}

	function calculate_discount_value($fee_discount,$original){
		$to_return = array();

		if($fee_discount['discount_type'] == "percentage"){
			$to_return['discount_id'] = $fee_discount['id'];
			$to_return['discount_value'] = ($original * $fee_discount['discount_value']) / 100;
			$to_return['after_discount'] = $original - ($original * $fee_discount['discount_value']) / 100;
		}

		if($fee_discount['discount_type'] == "fixed"){
			$to_return['discount_value'] = 0;
			if($fee_discount['discount_value'] >= $original){
				$to_return['discount_value'] = $original;
			}else{
				$to_return['discount_value'] = $fee_discount['discount_value'];
			}
			$to_return['after_discount'] = $original - $to_return['discount_value'];
			$to_return['discount_id'] = $fee_discount['id'];

		}

		return $to_return;
	}

	function get_prices($invoice){
		$prices = array();
		$prices['original'] = $invoice['paymentAmount'];
		$prices['discount'] = $invoice['paymentDiscount'];
		$prices['original_discounted'] = $invoice['paymentDiscounted'];
		$prices['tax_value'] = ($this->panelInit->settingsArray['paymentTax'] * $prices['original_discounted']) /100;
		$prices['total_with_tax'] = $prices['original_discounted'] + $prices['tax_value'];
		$prices['paid_value'] = $invoice['paidAmount'];

		if($invoice['dueDate'] < time()) {
			$prices['total_pending'] = ($prices['total_with_tax'] - $invoice['paidAmount']) + $invoice['fine_amount'];
			$prices['fine_amount'] = $invoice['fine_amount'];
		} else {
			$prices['total_pending'] = ($prices['total_with_tax'] - $invoice['paidAmount']);
			$prices['fine_amount'] = 0;
		}

		return $prices;
	}

	function fetch($id){

		if(!$this->panelInit->can( array("Invoices.View","Invoices.editPayment","Invoices.collInvoice","Invoices.payRevert") )){
			exit;
		}

		$payments = \payments::where('id',$id)->first()->toArray();
		$payments['paymentDate'] = $this->panelInit->unix_to_date($payments['paymentDate']);
		$payments['dueDate'] = $this->panelInit->unix_to_date($payments['dueDate']);

		$payments['paymentRows'] = json_decode($payments['paymentRows'],true);
		if(!is_array($payments['paymentRows'])){
			$payments['paymentRows'] = array();
			$payments['paymentRows'][] = array('title'=>$payments['paymentDescription'],'amount'=>$payments['paymentAmount']);
		}

		updateSeenNotificationMobHistory(Auth::guard('web')->user()->id, 'invoice', $id);

		return $payments;
	}

	function edit($id){

		if(!$this->panelInit->can( "Invoices.editPayment" )){
			exit;
		}

		$payments = \payments::find($id);
		$payments->paymentTitle = \Input::get('paymentTitle');
		if(\Input::has('fine_amount')){
		$payments->fine_amount = \Input::get('fine_amount');
		}else{
			$payments->fine_amount = '0';
		}
		if(\Input::has('paymentDescription')){
			$payments->paymentDescription = \Input::get('paymentDescription');
		}

		if(\Input::has('paymentRows')){
			$payments->paymentRows = json_encode(\Input::get('paymentRows'));

			$paymentAmount = 0;
			$paymentRows = \Input::get('paymentRows');
			foreach($paymentRows as $key => $value){
				$paymentAmount += $value['amount'];
			}
		}else{
			$paymentRows = array();
			$payments->paymentRows = json_encode($paymentRows);
			$paymentAmount = 0;
		}

		$payments->paymentAmount = $paymentAmount;
		$payments->paymentDiscounted = $paymentAmount;
		$payments->paymentDate = $this->panelInit->date_to_unix(\Input::get('paymentDate'));
		$payments->dueDate = $this->panelInit->date_to_unix(\Input::get('dueDate'));
		$payments->save();

		user_log('Payments', 'edit', 'Invoice ID #' . $id);

		// if($payments->paymentStatus == 0){
		// 	$this->calculate_discounts($payments->id);
		// }

		$payments->paymentDate = \Input::get('paymentDate');
		$payments->dueDate = \Input::get('dueDate');

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editPayment'],$this->panelInit->language['paymentModified'],$payments->toArray() );
	}

	function collect($id){

		if(!$this->panelInit->can( "Invoices.collInvoice" )){
			exit;
		}

		$payments = \payments::where('id',$id);
		if($payments->count() == 0){
			return;
		}
		$payments = $payments->first();

		$prices = $this->get_prices($payments->toArray());

		if($payments->toArray()['dueDate'] < time()) {
			$totalWithTax = $prices['total_with_tax'] + $prices['fine_amount'];
			$pendingAmount = $prices['total_pending'];
		} else {
			$totalWithTax = $prices['total_with_tax'];
			$pendingAmount = $prices['total_pending'] - $prices['fine_amount'];
		}

		if(bccomp(\Input::get('collectionAmount'), $pendingAmount,10) == 1){
			return $this->panelInit->apiOutput(false,"Invoice Collection","Collection amount is greater that invoice pending amount");
		}

		$paymentsCollection = new \paymentsCollection();
		$paymentsCollection->invoiceId = $id;
		$paymentsCollection->collectionAmount = \Input::get('collectionAmount');
		$paymentsCollection->collectionDate = $this->panelInit->date_to_unix(\Input::get('collectionDate'));
		$paymentsCollection->collectionMethod = \Input::get('collectionMethod');
		if(\Input::has('collectionNote')){
			$paymentsCollection->collectionNote = \Input::get('collectionNote');
		}
		$paymentsCollection->collectedBy = $this->data['users']->id;
		$paymentsCollection->save();

		$payments->paidAmount = $payments->paidAmount + $paymentsCollection->collectionAmount;

		// dd(
		// 	'payments-paidAmount: ' . $payments->paidAmount . ' - ' .
		// 	'paymentsCollection-collectionAmount: ' . $paymentsCollection->collectionAmount . ' - ' .
		// 	'totalWithTax: ' . $totalWithTax
		// );

		if($payments->paidAmount >= $totalWithTax){
			$payments->paymentStatus = 1;
		}else{
			$payments->paymentStatus = 2;
		}
		$payments->paidMethod = \Input::get('collectionMethod');
		$payments->paidTime = $this->panelInit->date_to_unix(\Input::get('collectionDate'));
		if(isset($prices['coupoun'])){
			$payments->discount_details = json_encode($prices['coupoun']);
		}
		$payments->save();

		$payments->paymentAmount = $totalWithTax;

		return $this->panelInit->apiOutput(true,"Invoice Collection","Collection completed successfully",$payments->toArray());
	}

	function revert($id){

		if(!$this->panelInit->can( "Invoices.payRevert" )){
			exit;
		}

		$paymentsCollection = \paymentsCollection::where('id',$id);
		if($paymentsCollection->count() == 0){
			return;
		}
		$paymentsGet = $paymentsCollection->first();
		$invoice = $paymentsGet->invoiceId;
		$paymentsCollection = $paymentsCollection->delete();

		//recalculate
		$totalPaid = 0;
		$paymentsCollection = \paymentsCollection::where('invoiceId',$invoice)->get();
		foreach ($paymentsCollection as $key => $value) {
			$totalPaid += $value['collectionAmount'];
		}

		$payments = \payments::where('id',$invoice);
		if($payments->count() == 0){
			return;
		}
		$payments = $payments->first();

		$prices = $this->get_prices($payments->toArray());

		if($totalPaid >= $prices['total_with_tax']){
			$payments->paymentStatus = 1;
		}elseif ($totalPaid == 0) {
			$payments->paymentStatus = 0;
		}else{
			$payments->paymentStatus = 2;
		}
		$payments->paidAmount = $totalPaid;
		$payments->save();

		user_log('Payments', 'revert', 'Invoice ID #' . $id);

		return $this->panelInit->apiOutput(true,"Revert Invoice Collection","Collection reverted successfully",$payments->toArray());
	}

	function paymentSuccess($uniqid){
		$payments = \payments::where('paymentUniqid',$uniqid)->first();
		if(\Input::get('verify_sign')){
			$payments->paymentStatus = 1;
			$payments->paymentSuccessDetails = json_encode(\Input::all());
			$payments->save();
		}
		return \Redirect::to('/#/payments');
	}

	public function paymentSuccessRedirectRequest() {
		$params = request()->all();

		// multi level checking params
		if(!count($params)) {
			return redirect('login');
		}
		if($params['pass_token'] != 'HTRO-PAYMENTS-39e9289a5b8328ecc4286da11076748716c41ec7fb94839a689f7dac5cdf5ba8bdc9a9acdc95b95245f80a00d58c9575c203ceb541507cce40dd5a96e9399f4a') {
			return redirect('login');
		}
		if(!isset($params['invoice_id'])) {
			return redirect('login');
		}
		if(!Payment::where(DB::raw('md5("id")'), $params['invoice_id'])->count()) {
			return redirect('login');
		}

		$payment = Payment::where(DB::raw('md5("id")'), $params['invoice_id'])->first();
		$payment->paymentStatus = 1;
		$payment->save();

		return redirect('/portal#/pay-fee');

		// http://cutebrains.devo/invoices/success/redirect-request?invoice_id=5&pass_token=HTRO-PAYMENTS-39e9289a5b8328ecc4286da11076748716c41ec7fb94839a689f7dac5cdf5ba8bdc9a9acdc95b95245f80a00d58c9575c203ceb541507cce40dd5a96e9399f4a
	}

	function PaymentData($id){
		if($this->data['users']->role != "admin" && $this->data['users']->role != "account") exit;
		$payments = \payments::where('id',$id)->first();
		if($payments->paymentSuccessDetails == ""){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['paymentDetails'],$this->panelInit->language['noPaymentDetails'] );
		}else{
			return $this->panelInit->apiOutput(true,null,null,json_decode($payments->paymentSuccessDetails,true) );
		}
	}

	function paymentFailed(){
		return \Redirect::to('/#/payments');
	}

	public function searchStudents($student){
		$students = \User::where('role','student')->where('fullName','like','%'.$student.'%')->orWhere('username','like','%'.$student.'%')->orWhere('email','like','%'.$student.'%')->get();
		$retArray = array();
		foreach ($students as $student) {
			$retArray[$student->id] = array("id"=>$student->id,"name"=>$student->fullName,"email"=>$student->email);
		}
		return json_encode($retArray);
	}

	public function importExcel() {
		if(!$this->panelInit->can("Invoices.addPayment")){
			exit;
		}

		if (\Input::hasFile('excelcsv')) {
			if ( $_FILES['excelcsv']['tmp_name'] ) {
				$student_types = [];
				$class_ids = [];
				$section_ids = [];
				if(request()->input('student_types') != '') {
					$student_types = explode(',', request()->input('student_types'));
				}
				if(request()->input('classes') != '') {
					$class_ids = explode(',', request()->input('classes'));
				}
				if(request()->input('sections') != '') {
					$section_ids = explode(',', request()->input('sections'));
				}

				if(count($class_ids) == 0 || (count($class_ids) == 1 && count($section_ids) == 0)) {
					return $this->panelInit->apiOutput(false, 'Incomplete import fees', 'Fee Group Type, class or section can not be empty.');
				}

				// Get payment students ----------------------------------------------
					User::$withoutAppends = true;
					$students = User::where('role','student')->where('activated','1');
					if(count($class_ids) > 0){
						$students = $students->whereIn('studentClass', $class_ids);
					}
					if(count($class_ids) == 1 && count($section_ids) > 0){
						$students = $students->whereIn('studentSection', $section_ids);
					}
					if(count($student_types) > 0){
						$students = $students->whereIn('studentType', $student_types);
					}
					$paymentStudent = $students->select('id')->get()->toArray();

					if(count($paymentStudent) <= 0) {
						return $this->panelInit->apiOutput(false, 'Incomplete import fees', 'There are no results for students according to your filter.');
					}
				// end Get payment students ----------------------------------------------

				$temp_filename = $_FILES['excelcsv']['tmp_name'];
				$readExcel = \Excel::load($temp_filename, function($reader) {
					$reader->noHeading();
				})->toArray();

				$items = [];
				$previewItems = [];

				foreach ($readExcel as $i => $row) {
					$item = [];
					if(count(array_unique($row)) == 1 && array_unique($row)[0] == null) {
						continue;
					}
					if (strpos($row[0], 'Note:') !== false) {
				    continue;
					}
					if(!is_null($readExcel[0][$i]) && $i > 0) {
						$item['fee_title'] = $readExcel[0][$i];
						for ($j = 1; $j < count($readExcel); $j++) {
							if (strpos($readExcel[$j][0], 'Note:') !== false) {
						    continue;
							}
							if($readExcel[$j] != null) {
								$insert = $readExcel[$j][$i];
								$item[$readExcel[$j][0]] = $insert;
							}
						}
						$items[] = $item;
					}

					// preview import --------------
					foreach ($row as $el_key => $element) {
						if($el_key > 0 && is_null($element)) {
							unset($row[$el_key]);
						}
						if(substr($element, 0, 1) == '*') {
							$row[$el_key] = substr($element, 1);
						}
					}
					if($i == 1 || $i == 2) {
						foreach ($row as $el_key => $element) {
							if($el_key > 0 && !is_null($element)) {
								$row[$el_key] = $this->panelInit->unix_to_date($element->timestamp);
							}
						}
					}
					$previewItems[] = $row;
					// end preview import -----------
				}

				if(request()->input('review_import') == 'false') {
					foreach ($items as $item) {
						foreach($paymentStudent as $key => $value){
							if($value['id'] == "" || $value['id'] == "0"){
								continue;
							}
							$payments = new \payments();
							$payments->paymentTitle = $item['fee_title'];
							$payments->paymentStudent = $value['id'];

							// payment rows ------------
							$fees_list = [];
							foreach ($item as $fee => $fee_value) {
								if(substr($fee, 0, 1) == '*') {
									$fees_list[] = [
										'title' => substr($fee, 1),
										'amount' => $fee_value
									];
								}
							}

							if(count($fees_list) <= 0) {
								return $this->panelInit->apiOutput(false, 'Incomplete import fees', 'Payment rows can not be empty.');
							}

							$payments->paymentRows = json_encode($fees_list);
							$paymentAmount = 0;
							foreach($fees_list as $key => $value){
								$paymentAmount += $value['amount'];
							}
							$payments->paymentAmount = $paymentAmount;
							$payments->paymentDiscounted = $paymentAmount;
							// -----------------

							$payments->paymentDate = $item['Fee start date']->timestamp;
							$payments->dueDate = $item['Fee due date']->timestamp;
							$payments->paymentUniqid = uniqid();
							$payments->paymentStatus = 0;
							$payments->fine_amount = $item['Late fee fine'];
							$payments->save();
						}
					}

					user_log('Payments', 'import_fees');

					return $this->panelInit->apiOutput(true, 'Import Fee status', 'Success import fees');
				} else {
					return response()->json([
						'preview_items' => true,
						'previewItems' => $previewItems
					]);
				}
			}
		}else{
			return $this->panelInit->apiOutput(false, $this->panelInit->language['Import'], $this->panelInit->language['specifyFileToImport']);
			exit;
		}
		exit;
	}

	public function downloadImportFeeSample() {
		return response()->download('storage/app/import fees of all months.xlsx');
	}

	function export($type){

		if(!$this->panelInit->can( "Invoices.Export" )){
			exit;
		}

		$classes_list = array();
		$sections_list = array();

		$classes = \classes::select('id','className')->get();
		foreach ($classes as $class) {
			$classes_list[$class->id] = $class->className ;
		}

		$sections = \sections::select('id','sectionName')->get();
		foreach ($sections as $section) {
			$sections_list[$section->id] = $section->sectionName;
		}

		user_log('Payments', 'export');

		if($type == "excel"){

			$return['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

			$data = array(1 => array ('Invoice ID','Title','Student','Class','Section','Amount','Paid Amount','Fine Amount','Date','Due Date','Status'));

			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName',
						'users.studentClass as studentClass',
						'users.studentSection as studentSection');
			$toReturn['totalItems'] = $toReturn['invoices']->count();
			$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->get();

			foreach ($toReturn['invoices'] as $key => $value) {
				$value->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
				$value->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);
				$value->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;
				if($value->paymentStatus == 1){
					$paymentStatus = "PAID";
				}elseif($value->paymentStatus == 2){
					$paymentStatus = "PARTIALLY PAID";
				}else{
					$paymentStatus = "UNPAID";
				}

				$student_class = "";
				if(isset($classes_list[$value->studentClass])){
					$student_class = $classes_list[$value->studentClass];
				}

				$student_section = "";
				if(isset($sections_list[$value->studentSection])){
					$student_section = $sections_list[$value->studentSection];
				}

				$data[] = array($value->paymentTitle,$value->paymentDescription,$value->fullName,$student_class,$student_section,$return['currency_symbol']." ".$value->paymentAmount,$return['currency_symbol']." ".$value->paidAmount,$value->fine_amount,$value->paymentDate,$value->dueDate,$paymentStatus);
			}

			\Excel::create('Payments-Sheet', function($excel) use($data) {

			    // Set the title
			    $excel->setTitle('Payments Sheet');

			    // Chain the setters
			    $excel->setCreator('Cutebrains')->setCompany('Virtu');

				$excel->sheet('Payments', function($sheet) use($data) {
					$sheet->freezeFirstRow();
					$sheet->fromArray($data, null, 'A1', true,false);
				});

			})->download('xls');

		}elseif ($type == "pdf") {
			$return['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

			$header = array ($this->panelInit->language['InvID'],$this->panelInit->language['title'],$this->panelInit->language['student'],$this->panelInit->language['class'],$this->panelInit->language['section'],$this->panelInit->language['Amount'],$this->panelInit->language['discoutedAmount'],$this->panelInit->language['paidAmount'],$this->panelInit->language['Date'],$this->panelInit->language['dueDate'],$this->panelInit->language['Status']);
			$data = array();

			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName',
						'users.studentClass as studentClass',
						'users.studentSection as studentSection');
			$toReturn['totalItems'] = $toReturn['invoices']->count();
			$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->limit('100')->get();

			foreach ($toReturn['invoices'] as $key => $value) {
				$value->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
				$value->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);
				$value->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;
				if($value->paymentStatus == 1){
					$paymentStatus = $this->panelInit->language['paid'];
				}elseif($value->paymentStatus == 2){
					$paymentStatus = $this->panelInit->language['ppaid'];
				}else{
					$paymentStatus = $this->panelInit->language['unpaid'];
				}

				$student_class = "";
				if(isset($classes_list[$value->studentClass])){
					$student_class = $classes_list[$value->studentClass];
				}

				$student_section = "";
				if(isset($sections_list[$value->studentSection])){
					$student_section = $sections_list[$value->studentSection];
				}

				$data[] = array($value->paymentTitle,$value->paymentDescription,$value->fullName,$student_class,$student_section,$return['currency_symbol']." ".$value->paymentAmount,$return['currency_symbol']." ".$value->paymentDiscount,$return['currency_symbol']." ".$value->paidAmount,$value->paymentDate,$value->dueDate,$paymentStatus);
			}

			$doc_details = array(
								"title" => "Payments",
								"author" => $this->data['panelInit']->settingsArray['siteTitle'],
								"topMarginValue" => 10
								);

			if( $this->panelInit->isRTL == "1" ){
				$doc_details['is_rtl'] = true;
			}

			$pdfbuilder = new \PdfBuilder($doc_details);

			$content = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
		        <thead><tr>";
				foreach ($header as $value) {
					$content .="<th style='width:15%;border: solid 1px #000000; padding:2px;'>".$value."</th>";
				}
			$content .="</tr></thead><tbody>";

			foreach($data as $row)
			{
				$content .= "<tr>";
				foreach($row as $col){
					$content .="<td>".$col."</td>";
				}
				$content .= "</tr>";
			}

	        $content .= "</tbody></table>";

			$pdfbuilder->table($content, array('border' => '0','align'=>'') );
			$pdfbuilder->output('Payments.pdf');

		}
	}

	public function exportAsReport($type, $ids) {
		if(!$this->panelInit->can( "Invoices.Export" )){
			exit;
		}

		$hashids = new Hashids('invoices_report');
		$target_payment_ids = $hashids->decode($ids);

		$classes_list = array();
		$sections_list = array();

		$classes = \classes::select('id','className')->get();
		foreach ($classes as $class) {
			$classes_list[$class->id] = $class->className ;
		}

		$sections = \sections::select('id','sectionName')->get();
		foreach ($sections as $section) {
			$sections_list[$section->id] = $section->sectionName;
		}

		user_log('Payments', 'export');

		if($type == "excel"){

			$return['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->whereIn('payments.id', $target_payment_ids)
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentRows as paymentRows',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paidMethod as paidMethod',
						'payments.dueDate as dueDate',
						'payments.paidTime as paidTime',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName',
						'users.studentClass as studentClass',
						'users.studentSection as studentSection',
						'users.admission_number as admission_number',
						'users.phoneNo as phoneNo'
						);
			$toReturn['totalItems'] = $toReturn['invoices']->count();
			$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->get();

			$main_items = [
				'Invoice ID','Title','Student',
				'Admission number', 'Phone number',
				'Class','Section',
				'Paid on',
				// 'Due Date',
				'Mode of Payment',
				'Status',
			];
			$custom_invoices_rows = Payment::getInvoiceRowsInArray($target_payment_ids);
			$total_items = array_merge(
				$main_items,
				$custom_invoices_rows,
				[
					'Fine amount',
					'(' . $return['currency_symbol'] .') '. 'Amount',
					'(' . $return['currency_symbol'] .') '. 'Paid Amount',
				]
			);

			$data = [1 => $total_items];
			$calc_counts_of_columns = [];
			$total_fine_amount = 0;
			$total_amount = 0;
			$total_paid_amount = 0;

			foreach ($toReturn['invoices'] as $key => $value) {
				$value->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
				$value->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);
				$value->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;
				if($value->paymentStatus == 1){
					$paymentStatus = "PAID";
				}elseif($value->paymentStatus == 2){
					$paymentStatus = "PARTIALLY PAID";
				}else{
					$paymentStatus = "UNPAID";
				}

				if(!is_null($value->paidTime)) {
					$value->paidTime = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paidTime);
				} else {
					$value->paidTime = '';
				}

				$student_class = "";
				if(isset($classes_list[$value->studentClass])){
					$student_class = $classes_list[$value->studentClass];
				}

				$student_section = "";
				if(isset($sections_list[$value->studentSection])){
					$student_section = $sections_list[$value->studentSection];
				}

				$rows_amount_array = [];
				$rows = json_decode($value->paymentRows);
				foreach ($custom_invoices_rows as $key => $header_row) {
					$inner_status = true;

					if(!isset($calc_counts_of_columns[$header_row])) {
						$calc_counts_of_columns[$header_row] = 0;
					}

					foreach ($rows as $key => $row) {
						if($header_row == $row->title && $inner_status) {
							$rows_amount_array[] = (float) $row->amount;
							$inner_status = false;
							$calc_counts_of_columns[$row->title] += (float) $row->amount;
						}
					}
					if($inner_status) {
						$rows_amount_array[] = '';
					}
				}

				$main_data = [
					$value->id,
					$value->paymentTitle,
					$value->fullName,
					$value->admission_number,
					$value->phoneNo,
					$student_class,
					$student_section,
					$value->paidTime,
					// $value->dueDate,
					$value->paidMethod,
					$paymentStatus,
				];

				// check set fine amount
				if($value->paymentAmount == $value->paidAmount) {
					$current_fine_amount = 0;
				} else {
					$current_fine_amount = (float) $value->fine_amount;
				}

				$data[] = array_merge(
					$main_data,
					$rows_amount_array,
					[
						$current_fine_amount,
						(float) $value->paymentAmount,
						(float) $value->paidAmount
					]
				);

				$total_fine_amount += $current_fine_amount;
				$total_amount += $value->paymentAmount;
				$total_paid_amount += $value->paidAmount;
			}

			// ADD CUSTOM COUNT ROW
			$temp_row_count = count($data[1]);
			$temp_counts_row = count($calc_counts_of_columns);
			$row_of_columns_counts = array_merge(array_fill(0, $temp_row_count - $temp_counts_row - 3, ''), $calc_counts_of_columns);

			array_push($row_of_columns_counts, $total_fine_amount, $total_amount, $total_paid_amount);

			// append counts row to main data
			$data = array_merge($data, [$row_of_columns_counts]);

			\Excel::create('Payments-Sheet', function($excel) use($data) {
		    // Set the title
		    $excel->setTitle('Payments Sheet');

		    // Chain the setters
		    $excel->setCreator('Cutebrains')->setCompany('Virtu');

				$excel->sheet('Payments', function($sheet) use($data) {
					$sheet->freezeFirstRow();
					$sheet->fromArray($data, null, 'A1', true,false);
				});
			})->download('xls');
		}elseif ($type == "pdf") {
			$return['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

			$main_header = array (
				$this->panelInit->language['InvID'],
				// $this->panelInit->language['title'],
				$this->panelInit->language['student'],
				'Admission number',
				'Phone number',
				$this->panelInit->language['class'],
				$this->panelInit->language['section'],
				'Paid on',
				// $this->panelInit->language['dueDate'],
				'Mode of payment',
				$this->panelInit->language['Status'],
				'Fine Amount',
			);

			$custom_invoices_rows = Payment::getInvoiceRowsInArray($target_payment_ids);
			$header = array_merge(
				$main_header,
				// $custom_invoices_rows,
				[
					$this->panelInit->language['Amount'],
					$this->panelInit->language['discoutedAmount'],
					$this->panelInit->language['paidAmount'],
				]
			);

			$data = array();

			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->whereIn('payments.id', $target_payment_ids)
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentRows as paymentRows',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentDiscount as paymentDiscount',
						'payments.paidAmount as paidAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paidMethod as paidMethod',
						'payments.paidTime as paidTime',
						'payments.dueDate as dueDate',
						'payments.paymentStudent as studentId',
						'payments.fine_amount as fine_amount',
						'users.fullName as fullName',
						'users.studentClass as studentClass',
						'users.studentSection as studentSection',
						'users.admission_number as admission_number',
						'users.phoneNo as phoneNo'
			);
			$toReturn['totalItems'] = $toReturn['invoices']->count();
			$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->limit('100')->get();

			foreach ($toReturn['invoices'] as $key => $value) {
				$value->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
				$value->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);
				$value->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;
				if($value->paymentStatus == 1){
					$paymentStatus = $this->panelInit->language['paid'];
				}elseif($value->paymentStatus == 2){
					$paymentStatus = $this->panelInit->language['ppaid'];
				}else{
					$paymentStatus = $this->panelInit->language['unpaid'];
				}

				if(!is_null($value->paidTime)) {
					$value->paidTime = Carbon::createFromTimestamp((integer) $value->paidTime)->format('Y/m/d');
				} else {
					$value->paidTime = '';
				}

				$student_class = "";
				if(isset($classes_list[$value->studentClass])){
					$student_class = $classes_list[$value->studentClass];
				}

				$student_section = "";
				if(isset($sections_list[$value->studentSection])){
					$student_section = $sections_list[$value->studentSection];
				}

				$rows_amount_array = [];
				$rows = json_decode($value->paymentRows);
				if(count($rows)) {
					foreach ($custom_invoices_rows as $key => $header_row) {
						$inner_status = true;
						foreach ($rows as $key => $row) {
							if($header_row == $row->title && $inner_status) {
								$rows_amount_array[] = (float) $row->amount;
								$inner_status = false;
							}
						}
						if($inner_status) {
							$rows_amount_array[] = '';
						}
					}
				}

				$main_data = array(
					$value->paymentTitle,
					// $value->paymentDescription,
					$value->fullName,
					$value->admission_number,
					$value->phoneNo,
					$student_class,
					$student_section,
					$value->paidTime,
					// $value->dueDate,
					$value->paidMethod,
					$paymentStatus,
					$value->fine_amount,
				);

				$data[] = array_merge(
					$main_data,
					// $rows_amount_array,
					[
						$return['currency_symbol']." ".$value->paymentAmount,
						$return['currency_symbol']." ".$value->paymentDiscount,
						$return['currency_symbol']." ".$value->paidAmount,
					]
				);
			}

			$doc_details = array(
				"title" => "Payments",
				"author" => $this->data['panelInit']->settingsArray['siteTitle'],
				"topMarginValue" => 10
			);

			if( $this->panelInit->isRTL == "1" ){
				$doc_details['is_rtl'] = true;
			}

			$pdfbuilder = new \PdfBuilder($doc_details);

			$content = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
		        <thead><tr>";
				foreach ($header as $value) {
					$content .="<th style='width:15%;border: solid 1px #000000; padding:2px;'>".$value."</th>";
				}
			$content .="</tr></thead><tbody>";

			foreach($data as $row)
			{
				$content .= "<tr>";
				foreach($row as $col){
					$content .="<td>".$col."</td>";
				}
				$content .= "</tr>";
			}

      $content .= "</tbody></table>";

			$pdfbuilder->table($content, array('border' => '0','align'=>'') );
			$pdfbuilder->output('Payments.pdf');
		}
	}

	public static function generateInvoice($user,$case){
		if($user->studentClass == "" || $user->studentClass == "0"){
			return;
		}

		// $feeAllocationUser = \fee_allocation::where('allocationType','student')->where('allocationWhen',$case)->where('allocationId',$user->id)->get()->toArray();
		// $feeAllocationClass = \fee_allocation::where('allocationType','class')->where('allocationWhen',$case)->where('allocationId',$user->studentClass)->get()->toArray();

		// $feeTypesArray = array();
		// $feeTypes = \fee_type::get();
		// foreach($feeTypes as $type){
		// 	$feeTypesArray[$type->id] = $type->feeTitle;
		// }

		// if(count($feeAllocationUser) > 0){
		// 	foreach ($feeAllocationUser as $allocatedUser) {

		// 		$paymentDescription = array();
		// 		$paymentAmount = 0;
		// 		$allocationValues = json_decode($allocatedUser->allocationValues,true);
		// 		foreach($allocationValues as $key => $value){
		// 			if(isset($feeTypesArray[$key])){
		// 				$paymentDescription[] = $feeTypesArray[$key];
		// 				$paymentAmount += $value;
		// 			}
		// 		}

		// 		$payments = new \payments();
		// 		$payments->paymentTitle = $allocatedUser->allocationTitle;
		// 		$payments->paymentDescription = implode(", ",$paymentDescription);
		// 		$payments->paymentStudent = $user->id;
		// 		$payments->paymentAmount = $paymentAmount;
		// 		$payments->paymentStatus = "0";
		// 		$payments->paymentDate = time();
		// 		$payments->paymentUniqid = uniqid();
		// 		$payments->save();

		// 	}
		// }

		// if(count($feeAllocationClass) > 0){
		// 	foreach ($feeAllocationClass as $allocatedUser) {

		// 		$paymentDescription = array();
		// 		$paymentAmount = 0;
		// 		$allocationValues = json_decode($allocatedUser['allocationValues'],true);
		// 		foreach($allocationValues as $key => $value){
		// 			if(isset($feeTypesArray[$key])){
		// 				$paymentDescription[] = $feeTypesArray[$key];
		// 				$paymentAmount += $value;
		// 			}
		// 		}

		// 		$payments = new \payments();
		// 		$payments->paymentTitle = $allocatedUser['allocationTitle'];
		// 		$payments->paymentDescription = implode(", ",$paymentDescription);
		// 		$payments->paymentStudent = $user->id;
		// 		$payments->paymentAmount = $paymentAmount;
		// 		$payments->paymentStatus = "0";
		// 		$payments->paymentDate = time();
		// 		$payments->paymentUniqid = uniqid();
		// 		$payments->save();

		// 	}
		// }
	}

	public function fetchPaymentMethods() {
		if(!$this->panelInit->can( "Invoices.Export" )){
			exit;
		}

		$payment_methods = Payment::payment_methods();

		return response()->json([
			'payment_methods' => $payment_methods
		]);
	}

	public function fetchUnpaidFeesForChart($title) {
		$section_titles_list = Section::distinct()->pluck('sectionName')->toArray();
		$section_titles = [];
		foreach ($section_titles_list as $key => $name) {
			$section_titles[] = "Section " . $name;
		}
		$dimensions = array_merge(['class'], $section_titles);

		$fee_title = $title;
		$now = Carbon::now();
		$startDate = $now->startOfYear()->timestamp;
		$endDate = $now->endOfYear()->timestamp;

		$payments = Payment::where('paymentTitle', $fee_title)
			->orWhere('paymentDescription', $fee_title)
			->where('paymentStatus', 0)
			->whereBetween('paymentDate', [$startDate, $endDate])
			->pluck('paymentStudent', 'id')
			->toArray();

		$unpaid_sections = [];
		$needed_users = User::where('role', 'student')->pluck('studentSection', 'id');
		foreach ($payments as $fee_id => $student_id) {
			if(isset($needed_users[$student_id])) {
				$section_id = $needed_users[$student_id];

				if(!isset($unpaid_sections[$section_id])) {
					$unpaid_sections[$section_id] = 1;
				} else {
					$unpaid_sections[$section_id] += 1;
				}
			}
		}

		$items = [];
		$classes = MClass::pluck('className', 'id')->toArray();
		$classes2 = MClass::pluck('className')->toArray();
		$counter = 0;
		foreach ($classes as $class_id => $class_name) {
			$sections = Section::where('classId', $class_id)->pluck('sectionName', 'id')->toArray();

			if($counter % 2 != 0) {
				$class_name = "\n" . $class_name;
			}
			$item = ['class' => $class_name];

			foreach ($sections as $section_id => $section_name) {
				if(in_array($section_id, array_keys($unpaid_sections))) {
					$item["Section " . $section_name] = $unpaid_sections[$section_id];
				}
			}

			$items[] = $item;
			$counter ++;
		}

		$classes2_list = [];
		foreach ($classes2 as $key => $class_name) {
			if($key % 2 == 0) {
				$classes2_list[] = $class_name;
			} else {
				$classes2_list[] = "\n" . $class_name;
			}
		}

		return response()->json([
			'dimensions' => $dimensions,
			'source' => $items,
			'classes' => $classes2_list
		]);
	}

	public function fetchUniqueFeeTitles() {
		$data = Payment::groupBy('paymentTitle')->get()->toArray();
		return response()->json([
			'fee_list' => $data
		]);
	}
}