var CuteBrains = angular.module('examsListController', []);

CuteBrains.controller('examsListController', function(dataFactory,$rootScope,$scope,$sce) {
    $scope.isLoading = false;
    $scope.examsList = {};
    $scope.school_terms = {};
    $scope.classes = {};
    $scope.subjects = {};
    $scope.userRole ;
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.form.examSchedule = {};
    $scope.teachers = {};
    $scope.searchForm = { term: "", exam: "", class: "", section: "", subject: "" };
    $scope.searchTerms = {};
    $scope.searchExams = {};
    $scope.searchClasses = {};
    $scope.searchSections = {};
    $scope.searchSubjects = {};
    $scope.selectedClass = "";
    $scope.selectedSection = "";

    $scope.$on('$viewContentLoaded', function() {
    	document.title = $('meta[name="site_title"]').attr('content') + ' | Exams';
    });

    $scope.getSchoolTerm = function(term_id) {
    	return $scope.school_terms.find(x => x.id == term_id);
    }

    $scope.showModal = false;
    $scope.studentProfile = function(id){
        dataFactory.httpRequest('index.php/students/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalClass = data.modalClass;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    $scope.preLoadList = function( )
    {
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/preLoad').then(function(data) {
            $scope.searchTerms = data.mixedArray;
            // $scope.searchExams = data.exams;
            $scope.searchClasses = data.classes;
            $scope.school_terms = data.school_terms;
            $scope.classes = data.getclasses;
            $scope.subjectsList = data.subjects;
            $scope.teachers = data.teachers;
            // $scope.subject_object = data.subject_object;
            // $scope.sub_subject_object = data.sub_subject_object;
            $scope.subject_with_subsubject_lists = data.subject_with_subsubject_lists;
            $scope.userRole = data.userRole;
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
            }, 300);
            showHideLoad(true);
        });
    }

    $scope.changeTermForSearch = function(){
        let selectedTerm = $('.search-term-form').find(':selected').data('idnex');
        let termData = $scope.searchTerms[selectedTerm].exams;
        var classId = $scope.searchForm.class;
        var finalExams = [];
        angular.forEach(termData, function(value, key) {
            // console.log( classId );
            // console.log( value.classesIds );
            angular.forEach( value.classesIds, function(oneClass, index){
                if( oneClass == classId ) { finalExams.push(value); }
            });
        });
        $scope.searchExams = finalExams;

        $scope.searchForm.exam = "";
        // $scope.searchForm.class = "";
        // $scope.searchForm.section = "";
        $scope.searchForm.subject = "";
    }

    $scope.changeExamForSearch = function(){
        var selectedTerm = $('.search-term-form').find(':selected').data('idnex');
        var selectedExam = $('.search-exam-form').find(':selected').data('idnex');
        // var classData = $scope.searchTerms[selectedTerm]['exams'][selectedExam].classes;
        var examId = $scope.searchForm.exam;
        var subjectData = [];
        angular.forEach($scope.searchTerms[selectedTerm]['exams'], function(oneExam, index){
            if( oneExam.id == examId ) { subjectData = oneExam.subjects; }
        });
        // var subjectData = $scope.searchTerms[selectedTerm]['exams'][selectedExam].subjects;
        // $scope.searchClasses = classData;
        $scope.searchSubjects = subjectData;
        // $scope.searchForm.class = "";
        // $scope.searchForm.section = "";
        // $scope.searchForm.subject = "";
    }

    $scope.changeClassForSearch = function(){
        // let selectedTerm = $('.search-term-form').find(':selected').data('idnex');
        // let selectedExam = $('.search-exam-form').find(':selected').data('idnex');
        let selectedClass = $('.search-class-form').find(':selected').data('idnex');
        
        let sectionData = $scope.searchClasses[selectedClass].sections;
        $scope.searchSections = sectionData;
        $scope.searchForm.section = "";
        $scope.searchForm.term = "";
        $scope.searchForm.exam = "";
        $scope.searchForm.subject = "";
    }

    $scope.doFilterExams = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/filterAll', 'POST', {}, $scope.searchForm).then(function(data) {
            $scope.examsList = data.exams;
            $scope.selectedClass = data.markForm.class;
            $scope.selectedSection = data.markForm.section;
            
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
            }, 300);
            showHideLoad(true);
        });
    }

    $scope.getList = function( )
    {
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/listAll').then(function(data) {
            $scope.examsList = data.exams;
            $scope.school_terms = data.school_terms;
            $scope.classes = data.classes;
            $scope.subjectsList = data.subjects;
            $scope.teachers = data.teachers;
    
            // $scope.subject_object = data.subject_object;
            // $scope.sub_subject_object = data.sub_subject_object;
            $scope.subject_with_subsubject_lists = data.subject_with_subsubject_lists;
            
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
            }, 300);

            $scope.userRole = data.userRole;
            showHideLoad(true);
        });
    }

    // $scope.getList();
    $scope.preLoadList();

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.classId}).then(function(data) {
            $scope.subjects = data.subjects;
            $scope.sections = data.sections;
        });
    }

    $scope.notify = function(id){
        var confirmNotify = confirm($rootScope.phrase.sureMarks);
        if (confirmNotify == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/examsList/notify/'+id,'POST',{},$scope.form).then(function(data) {
                apiResponse(data,'add');
                showHideLoad(true);
            });
        }
    }

    $scope.addMSCol = function(){
        var colTitle = prompt("Please enter column title");
        if (colTitle != null) {
            if(typeof $scope.form.examMarksheetColumns == "undefined"){
                $scope.form.examMarksheetColumns = [];
            }

            $i = 1;
            angular.forEach($scope.form.examMarksheetColumns, function(value, key) {
                if($i <= parseInt(value.id)){
                    $i = parseInt(value.id) + 1;
                }
            });

            $scope.form.examMarksheetColumns.push({'id':$i,'title':colTitle});
        }
    }

    $scope.removeMSCol = function(col,$index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            $scope.form.examMarksheetColumns.splice($index,1);
        }
    }

    $scope.addScheduleRow = function(){
        if(typeof $scope.form.examSchedule == "undefined") { $scope.form.examSchedule = []; }
        $scope.form.examSchedule.push( {
        	'subject': '',
        	'subject_type': 'main',
        	'name': '',
        	'stDate': '',
            'teachers': [],
            'start_time': '',
            'end_time': '',
        	'pass_marks': $scope.form.main_pass_marks,
        	'max_marks': $scope.form.main_max_marks
        } );

        if( $scope.views.add == true )
        {
            setTimeout(function(){
                $.each( $('.schedule-table .startTimePicker'), function (key, valueOfElement) {
                    let value = $scope.form.examSchedule[key].start_time;
                    if( value )
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: value });
                    }
                    else
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: '' });
                        $(this).val('');
                    }
    
                });
                $.each( $('.schedule-table .endTimePicker'), function (key, valueOfElement) {
                    let value = $scope.form.examSchedule[key].end_time;
                    if( value )
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: value });
                    }
                    else
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: '' });
                        $(this).val('');
                    }
                });
                $('select[multiple]').selectpicker('destroy');
                $('select[multiple]').selectpicker();
            }, 300);
        }
        else if( $scope.views.edit == true )
        {
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $.each( $('.edit-schedule-table .startTimePicker'), function (key, valueOfElement) {
                    let value = $scope.form.examSchedule[key].start_time;
                    if( value )
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: value });
                    }
                    else
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: '' });
                        $(this).val('');
                    }
    
                });
                $.each( $('.edit-schedule-table .endTimePicker'), function (key, valueOfElement) {
                    let value = $scope.form.examSchedule[key].end_time;
                    if( value )
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: value });
                    }
                    else
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: '' });
                        $(this).val('');
                    }
                });
                $('select[multiple]').selectpicker();
            }, 500);
        }
    }

    $scope.removeRow = function(row,index){
        $scope.form.examSchedule.splice(index,1);
    }

    $scope.saveAdd = function(){
        showHideLoad();
        angular.forEach($scope.form.examSchedule, function(subjectLine, key) {
            var finalId = 0;
            var str = subjectLine.subject;
            var subjectId = str.split("_");
            if( subjectId.length ) finalId = subjectId[1];
            else
            {
                swal.fire({ type: "error", title: 'Error', html: 'An error occured while processing your request.', timer: 4500 });
                showHideLoad(true);
                return;
            }
            $scope.form.examSchedule[key].subject = finalId;
        });
        $scope.isLoading = true;
        dataFactory.httpRequest('index.php/examsList','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                // $scope.examsList.push(response);
                // $scope.doFilterExams();
                $scope.changeView('list');
            }
            showHideLoad(true);
            $scope.isLoading = false;
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            setTimeout(function(){
                $('select[multiple]').selectpicker('destroy');
                $.each( $('.edit-schedule-table .startTimePicker'), function (key, valueOfElement) {
                    let value = $scope.form.examSchedule[key].start_time;
                    if( value )
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: value });
                        $(this).val(value);
                    }
                    else
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: '' });
                        $(this).val('');
                    }
    
                });
                $.each( $('.edit-schedule-table .endTimePicker'), function (key, valueOfElement) {
                    let value = $scope.form.examSchedule[key].end_time;
                    if( value )
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: value });
                        $(this).val(value);
                    }
                    else
                    {
                        $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: '' });
                        $(this).val('');
                    }
                });
                $.each( $('.edit-schedule-table .teacherPicker'), function (key, valueOfElement) {
                    let value = $scope.form.examSchedule[key].teachers;
                    if( value )
                    {
                        // $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: value });
                        $(this).val(value);
                    }
                    else
                    {
                        // $(this).timepicker({ showInputs: false, minuteStep: 30, defaultTime: '' });
                        $(this).val('');
                    }
                });
                $('select[multiple]').selectpicker();
            }, 500);
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        angular.forEach($scope.form.examSchedule, function(subjectLine, key) {
            var finalId = 0;
            var str = subjectLine.subject;
            var subjectId = str.split("_");
            if( subjectId.length ) finalId = subjectId[1];
            else
            {
                swal.fire({ type: "error", title: 'Error', html: 'An error occured while processing your request.', timer: 4500 });
                showHideLoad(true);
                return;
            }
            $scope.form.examSchedule[key].subject = finalId;
        });
        dataFactory.httpRequest('index.php/examsList/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                // $scope.examsList = apiModifyTable($scope.examsList,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/examsList/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.examsList.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.marks = function(exam){
        $scope.form.exam = exam.id;
        $scope.markClasses = [];

        try{
            exam.examClasses = JSON.parse(exam.examClasses);
        }catch(e){ }

        angular.forEach($scope.classes, function(value, key) {
            angular.forEach(exam.examClasses, function(value_) {
                if(parseInt(value.id) == parseInt(value_)){
                    $scope.markClasses.push(value);
                }
            });
        });
        $scope.changeView('premarks');
    }

    $scope.newmarks = function( examId, subjectId ){
        $scope.searchForm.exam = examId;
        $scope.searchForm.subject = subjectId;
        showHideLoad();
        let send_data = { exam: examId, classId: $scope.selectedClass, sectionId: $scope.selectedSection, subjectId: subjectId };
        dataFactory.httpRequest('index.php/examsList/getMarks','POST', {}, send_data).then(function(data) {
            $scope.form.exam = examId;
            $scope.form.classId = $scope.searchForm.class;
            $scope.form.subjectId = subjectId;
            $scope.form.respExam = data.exam;
            $scope.form.respClass = data.class;
            $scope.form.respSubject = data.subject;
            $scope.form.respStudents = data.students;
            $scope.changeView('marks');
            showHideLoad(true);
        });
    }

    $scope.startAddMarks = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/getMarks','POST',{},$scope.form).then(function(data) {
            $scope.form.respExam = data.exam;
            $scope.form.respClass = data.class;
            $scope.form.respSubject = data.subject;
            $scope.form.respStudents = data.students;

            $scope.changeView('marks');
            showHideLoad(true);
        });
    }

    $scope.saveNewMarks = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/saveMarks/'+$scope.form.exam+"/"+$scope.form.classId+"/"+$scope.form.subjectId,'POST',{},$scope.form).then(function(data) {
            apiResponse(data,'add');
            $scope.changeView('list');
            showHideLoad(true);
        });
    }

    $scope.examDetails = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('examDetails');
            showHideLoad(true);
        });
    }

    $scope.changeView = function (view) {
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        if( view == "list" )
        {
            // $scope.getList();
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.premarks = false;
        $scope.views.marks = false;
        $scope.views.examDetails = false;
        $scope.views[view] = true;
    }

    $scope.changeSubject = function (key, type) {
        // let subject = $scope.form.examSchedule[key].subject;
        if( type == 'add' ) { var scopeData = 'addSubject-' + key; } else { var scopeData = 'editSubject-' + key; }
        let selectedSubject = $('.' + scopeData).find(':selected').data('idnex');
        let subjectData = $scope.subject_with_subsubject_lists[selectedSubject];
        // $scope.form.examSchedule[key].subject = subjectData.realId;
        $scope.form.examSchedule[key].subject_type = subjectData.type;
        $scope.form.examSchedule[key].name = subjectData.subjectTitle;
    }

    $scope.in_array = function( needle, haystack )
    {
        for( var key in haystack ) { if( needle === haystack[key] ) return true; }
        return false;
    }
});