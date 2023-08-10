<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    Laravel
                </div>

                <div class="links">
                    <a href="https://laravel.com/docs">Docs</a>
                    <a href="https://laracasts.com">Laracasts</a>
                    <a href="https://laravel-news.com">News</a>
                    <a href="https://blog.laravel.com">Blog</a>
                    <a href="https://nova.laravel.com">Nova</a>
                    <a href="https://forge.laravel.com">Forge</a>
                    <a href="https://vapor.laravel.com">Vapor</a>
                    <a href="https://github.com/laravel/laravel">GitHub</a>
                </div>
            </div>
        </div>
    </body>
</html>


<!-- scheduler.php -->
<?php 
include("config.php");
include("functions.php");
session_start();
if(!$_SESSION['scheduler_qry']){die('invalid');}

//Shift Schedules
$jundrie=sqlsrv_fetch_array(sqlsrv_query($conn,"select * from zzz_users where id='".$_SESSION['schedule_id']."'"));
$sf="";
    $sq=sqlsrv_query($conn,"select * from hrshift where SeqID in (".$jundrie['shifts'].") order by ShiftID");
    while($s=sqlsrv_fetch_array($sq)){
        if($s['SeqID']==1){
            $s['ShiftID']="11pm - 7am";
            $s['ShiftDesc']="11pm - 7am";
        }
        if($s['SeqID']==2){
            $s['ShiftID']="7am - 3pm";
            $s['ShiftDesc']="7am - 3pm";
        }
        if($s['SeqID']==3){
            $s['ShiftID']="3pm - 11pm";
            $s['ShiftDesc']="3pm - 11pm";
        }
        $sf.='<option value="'.$s['SeqID'].'">'.$s['ShiftID'].'('.$s['ShiftDesc'].')';
    }   
$sf.='<option value="10000">RESTDAY';



//Week Identification
if(!isset($_GET['date'])){
    $date = get_first_monday($_SESSION['schedule_start']);
    //$date = '2019-06-10';
}
else{
    $date = $_GET['date'];
}

//$date='2019-06-10';
if(date('N',strtotime($date)) <> 1){
    die('Invalid Date!');
}
$previous_date = date('Y-m-d',strtotime("-7 days",strtotime($date)));
$next_date = date('Y-m-d',strtotime("+7 days",strtotime($date)));

//Groups
if(!isset($_GET['group']) || $_GET['group']=='ALL'){
    $group_qry = "";
    $group_now = "ALL";
}
else{
    if($_GET['group']=='NO GROUP'){
        $group_qry = " and (g.group_name='' OR g.group_name is null)";        
    }
    else{
        $group_qry = " and g.group_name='".$_GET['group']."'";      
    }
    $group_now = strtoupper($_GET['group']);
    //echo $group_now;
    //die();
}

$groups = '';
$group_query = sqlsrv_query($conn,"select ISNULL(g.group_name,'NO GROUP') as gr, count(g.empid) as total_emp from zzz_shift_group g 
    left join viewhrempmaster e on e.empid=g.empid where g.id>0 ".trim($_SESSION['scheduler_qry'])." group by g.group_name order by g.group_name");

while($rg = sqlsrv_fetch_array($group_query)){  
   $groups .= '<li><a href="scheduler.php?group='.$rg['gr'].'&date='.$date.'"><i class="fa fa-user"></i> '.strtoupper($rg['gr']).' ('.$rg['total_emp'].') </a></li>';
}



$data='';
$xqr = "";
$subqr = "";
if($_SESSION['type']==1){
    $xqr = " and g.section = '".ltrim(strtolower($_SESSION['schedule_username']),'s')."'";
    $subqr = " and x.section = '".ltrim(strtolower($_SESSION['schedule_username']),'s')."'";
}
else{
    $xqr = " and dept = '".$_SESSION['schedule_username']."'";
    $subqr = " and x.dept = '".$_SESSION['schedule_username']."'";
}

$sql1="INSERT INTO [dbo].[zzz_shift_v2]([empid], [effectivityDate], [mon], [tue], [wed], [thu], [fri], [sat], [sun],
[dept], [section], [deptdesc], [sectiondesc], [fullname], [effectivityEnd], [group_name] )";


$sql2="select g.EmpID,'".$date."',NULL,NULL,NULL,NULL,NULL,NULL,NULL,112 as DeptID,111 as SectionID,'dept' as DeptDesc,'section' as section,
e.first_name as FullName,DATEADD(DAY,6,'".$date."'),g.group_name
from zzz_shift_group g left join users e on e.employee_number=g.empid where g.empid<>'' ".$xqr." ".$group_qry."
AND NOT EXISTS (Select * from [zzz_shift_v2] x where e.employee_number=x.empid and x.effectivityDate='".$date."' ".$subqr." )";



$sqlcombined = $sql1 ." ".$sql2;
// print_r ($sqlcombined);
$insert_if_not_exist = sqlsrv_query($conntwo,$sqlcombined);

// $insert_if_not_exist = sqlsrv_query($conntwo,"
// INSERT INTO [dbo].[zzz_shift_v2]([empid], [effectivityDate], [mon], [tue], [wed], [thu], [fri], [sat], [sun],
// [dept], [section], [deptdesc], [sectiondesc], [fullname], [effectivityEnd], [group_name] )

// select g.EmpID,'".$date."',NULL,NULL,NULL,NULL,NULL,NULL,NULL,e.DeptID,e.SectionID,e.DeptDesc,e.section,e.FullName,DATEADD(DAY,6,'".$date."'),g.group_name
// from zzz_shift_group g left join JUNDRIE_EmpReports e on e.empid=g.empid where g.empid<>'' ".$xqr." ".$group_qry."
// AND NOT EXISTS (Select * from [zzz_shift_v2] x where e.empid=x.empid and x.effectivityDate='".$date."' ".$subqr." )");



$header='';
for($y = 0; $y<=6; $y++){
    $deh = date('Ymd', strtotime("+".$y." days",strtotime($date)));
    $is_readonlyh = is_readonly(date('Y-m-d', strtotime("+".$y." days",strtotime($date))));
    if($is_readonlyh==0){
        $header.='<th class="head_title has-success" style="text-align:center"><select class="form-control pr" id="'.$deh.'" name="'.$y.'"><option>Select</option>'.$sf.'</select>     <br>
            '.date('M-d (D)', strtotime("+".$y." days",strtotime($date))).'        
        </th>';
    }
    else{
        // Temporarily disabled due to dili mangfile na secretaries. bweset!
        /*
        $header.='<th class="head_title has-success" style="text-align:center"><br>
            '.date('M-d (D)', strtotime("+".$y." days",strtotime($date))).'        
        </th>';
        */
        $header.='<th class="head_title has-success" style="text-align:center"><select class="form-control pr" id="'.$deh.'" name="'.$y.'"><option>Select</option>'.$sf.'</select>     <br>
            '.date('M-d (D)', strtotime("+".$y." days",strtotime($date))).'        
        </th>';
    }
}

$q = sqlsrv_query($conn,"select g.*,CONVERT(varchar(23), g.effectivityDate, 121) as effectivityDate from [zzz_shift_v2] g where g.empid<>'' and g.effectivityDate='".$date."' ".trim($_SESSION['scheduler_qry'])." ".$group_qry." order by g.fullname");

while($r = sqlsrv_fetch_array($q)){  
    $data.=
        '<tr class="d-flex">
            <td><input type="checkbox" class="exclude" name="'.$r['id'].'" id="'.$r['id'].'"></td>    
            <td style="width:30%; vertical-align:middle">'.$r['fullname'].'</td>';
            for($x = 0; $x<=6; $x++){    

                $default='';
                $disp = '';
                $field = num_to_day($x);
                if($r[$field]>0 && $r[$field]<10000){
                    $shift = sqlsrv_fetch_array(sqlsrv_query($conn,"select * from hrshift where SeqID = '".$r[$field]."'"));
                    $default='<option value="'.$shift['SeqID'].'" selected="selected">'.$shift['ShiftID'].'('.$shift['ShiftDesc'].')</option>';
                    $disp = '<span style="font-size:11px;">'.$shift['ShiftID'].'</span>';
                }
                if($r[$field]==10000){
                    $default='<option value="10000" selected="selected">RESTDAY</option>';
                    $disp = 'RESTDAY';
                }

                $de = date('Ymd', strtotime("+".$x." days",strtotime($r['effectivityDate'])));
                $is_readonly = is_readonly(date('Y-m-d', strtotime("+".$x." days",strtotime($r['effectivityDate']))));
                if($is_readonly==1){
                    // Temporarily disabled due to dili mangfile na secretaries. bweset!
                    //$data.='<td align="center">'.$disp.'</td>';
                    $data.='<td align="center"><select class="form-control shift_select '.$de.'" name="'.$r['id'].'x'.$x.'" id="'.$r['id'].$de.'">
                            <option>Select</option>
                            '.$sf.''.$default.'
                        </select></td>';
                }
                elseif($is_readonly==2){
                    // Temporarily disabled due to dili mangfile na secretaries. bweset!
                    /*
                    $data.='<td align="center" class="has-error"><select class="form-control cs_select '.$de.'" name="'.$r['id'].'x'.$x.'" id="'.$r['id'].$de.'">
                            <option>Select</option>
                            '.$sf.''.$default.'
                        </select></td>';
                    */
                    $data.='<td align="center"><select class="form-control shift_select '.$de.'" name="'.$r['id'].'x'.$x.'" id="'.$r['id'].$de.'">
                            <option>Select</option>
                            '.$sf.''.$default.'
                        </select></td>';
                
                }
                else{
                    $data.='<td align="center"><select class="form-control shift_select '.$de.'" name="'.$r['id'].'x'.$x.'" id="'.$r['id'].$de.'">
                            <option>Select</option>
                            '.$sf.''.$default.'
                        </select></td>';
                }
            }

    $data.='</tr>';
}




?>
<!DOCTYPE html>

<html lang="en">
  
    <head>
        <meta charset="utf-8" />
        <title>Scheduler v2</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <meta content="Preview page of Metronic Admin Theme #1 for " name="description" />
        <meta content="" name="author" />
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="assets/google.css" rel="stylesheet" type="text/css" />
        <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
        <!-- END GLOBAL MANDATORY STYLES -->
  
        <!-- BEGIN THEME GLOBAL STYLES -->
        <link href="assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/global/css/components-rounded.min.css" rel="stylesheet" id="style_components" type="text/css" />
        <link href="assets/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
        <!-- END THEME GLOBAL STYLES -->
        <!-- BEGIN PAGE LEVEL STYLES -->
        <link href="assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />

        <!-- END PAGE LEVEL STYLES -->
        <!-- BEGIN THEME LAYOUT STYLES -->
        <link href="assets/layouts/layout/css/layout.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/layouts/layout/css/themes/darkblue.min.css" rel="stylesheet" type="text/css" id="style_color" />
        <link href="assets/layouts/layout/css/custom.min.css" rel="stylesheet" type="text/css" />
        <!-- END THEME LAYOUT STYLES -->
        <link rel="shortcut icon" href="favicon.ico" /> 
        <style>
            input[type=checkbox] {
                zoom: 1.5;
            }
            .table-striped > tbody > tr:nth-child(2n+1) > td, .table-striped > tbody > tr:nth-child(2n+1) > th {
               background-color: #F5F4EE;
            }
            .head_title{
                font-size:14px;
                font-weight:bold;
                color:blue;
            }

        </style>
    </head>
    <!-- END HEAD -->

    <body class="page-header-fixed page-container-bg-solid page-content-white page-full-width">
        <div class="page-wrapper">
            <!-- BEGIN HEADER -->
            <?php include("header.php"); ?>
            <!-- END HEADER -->
            <!-- BEGIN HEADER & CONTENT DIVIDER -->
            <div class="clearfix"> </div>
            <!-- END HEADER & CONTENT DIVIDER -->
            <!-- BEGIN CONTAINER -->
            <div class="page-container">
               
                <!-- BEGIN CONTENT -->
                <div class="page-content-wrapper">
                    <!-- BEGIN CONTENT BODY -->
                    <div class="page-content"> 
                        <a href="landing.php" class="btn btn-sm yellow-crusta">Back to home</a><br><br>
                        <div class="modal fade" id="reason_modal" tabindex="-1" role="basic" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                        <h4 class="modal-title">Change Schedule</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="scroller" style="height:150px" data-always-visible="1" data-rail-visible1="1">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Reason</label>
                                                <div class="col-md-9">
                                                    <input type="hidden" name="reason_id" id="reason_id">
                                                    <input type="hidden" name="reason_day" id="reason_day">
                                                    <input type="hidden" name="reason_selected" id="reason_selected">
                                                    <input type="text" name="reason" id="reason" class="form-control" placeholder="Specify reason for change schedule">
                                                </div>
                                            </div>
                                            
                                           
                                        </div>                                       
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn dark btn-outline" data-dismiss="modal">Cancel</button>
                                        <a href="javascript:void(0)" class="btn green btn-outline" onclick="submit_cs();">Submit</a>
                                    </div>
                                </div>
                            </div>
                        </div>   
                        <div class="row">
                            <div class="col-md-12">
                                <!-- BEGIN TODO SIDEBAR -->
                                <div class="todo-ui">
                                    
                                    <!-- END TODO SIDEBAR -->
                                    <!-- BEGIN TODO CONTENT -->
                                    <div class="todo-content">
                                        <div class="portlet light ">
                                            <!-- PROJECT HEAD -->
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="icon-bar-chart font-green-sharp hide"></i>           
                                                    <span class="caption-subject font-green-sharp bold uppercase">SCHEDULER</span>
                                                    <span class="caption-helper"><?php echo $group_now;?></span>
                                                </div>
                                                <div class="actions">
                                                    <div class="btn-group">
                                                        <a class="btn btn-sm blue" href="javascript:;" data-toggle="dropdown" aria-expanded="false">
                                                            <i class="fa fa-users"></i> Change Group
                                                            <i class="fa fa-angle-down "></i>
                                                        </a>
                                                        <ul class="dropdown-menu pull-right">
                                                            <?php 
                                                            echo $groups;
                                                            ?>
                                                            
                                                            <li class="divider"> </li>
                                                            <li>
                                                                <a href="scheduler.php?group=ALL&date=<?php echo $date;?>"> ALL EMPLOYEE </a>
                                                            </li>
                                                            
                                                        </ul>
                                                    </div>
                                                    <div class="btn-group btn-group-solid">
                                                        <a href="scheduler.php?group=<?php echo $group_now; ?>&date=<?php echo $previous_date;?>" class="btn purple"><< Previous</a>
                                                        <button type="button" class="btn default"><?php echo date('F d, Y',strtotime($date))?></button>
                                                        <a href="scheduler.php?group=<?php echo $group_now; ?>&date=<?php echo $next_date;?>" class="btn purple">Next >></a>
                                                    </div>
                                                    
                                                </div>                                               
                                            </div>
                                            <!-- end PROJECT HEAD -->
                                            <div class="portlet-body">
                                                <form action="index.php?act=submit" method="post" id="group_form">
                                                    <input type="hidden" name="group_name" id="group_name">
                                                    <table class="table table-condensed table-striped">
                                                       <thead>
                                                           <tr align="center">
                                                                <th class="head_title">Exclude</th>                                                              
                                                                <th style="width:20%" class="head_title">Fullname</th>
                                                                <?php echo $header?>
                                                           </tr>
                                                       </thead>
                                                       <tbody><?php echo $data;?></tbody>
                                                    </table>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END TODO CONTENT -->
                                </div>
                            </div>
                            <!-- END PAGE CONTENT-->
                        </div>
                    </div>
                    <!-- END CONTENT BODY -->
                </div>
                <!-- END CONTENT -->
                
            </div>
            <!-- END CONTAINER -->
            <!-- BEGIN FOOTER -->
            <?php include("footer.php"); ?>
            
            <!-- END FOOTER -->
        </div>
       
       
        <!-- BEGIN CORE PLUGINS -->
            <script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
            <script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
            <script src="assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
            <script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
            <script src="assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
            <script src="assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
            <!-- END CORE PLUGINS -->
            <!-- BEGIN PAGE LEVEL PLUGINS -->
            <script src="assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
            <script src="assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
             <script src="assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
            <!-- END PAGE LEVEL PLUGINS -->
            <!-- BEGIN THEME GLOBAL SCRIPTS -->
            <script src="assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
            <script src="assets/global/scripts/app.min.js" type="text/javascript"></script>
            <!-- END THEME GLOBAL SCRIPTS -->
            <!-- BEGIN PAGE LEVEL SCRIPTS -->
            <script src="assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
            <script src="assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
            <!-- END PAGE LEVEL SCRIPTS -->
            <!-- BEGIN THEME LAYOUT SCRIPTS -->
            <script src="assets/layouts/layout/scripts/layout.min.js" type="text/javascript"></script>
            <script src="assets/layouts/layout/scripts/demo.min.js" type="text/javascript"></script>
            <script src="assets/layouts/global/scripts/quick-sidebar.min.js" type="text/javascript"></script>
            <script src="assets/layouts/global/scripts/quick-nav.min.js" type="text/javascript"></script>
        <!-- END THEME LAYOUT SCRIPTS -->
        <script>
            $('.pr').change(function(){
                var id = $(this).attr('id');
                var val = $(this).val();
                $('.exclude').each(function() {
                    if($(this).is(":checked")){

                    }
                    else{
                        $('#'+$(this).attr('id')+id).val(val).change();
                        db_save($('#'+$(this).attr('id')+id).attr('name'),val);
                    }                    
                });                
            });

            $('.shift_select').change(function(){
                var id = $(this).attr('name');
                var val = $(this).val();
                db_save(id,val);
            });

            $('.cs_select').change(function(){
                $('#reason_id').val('');
                $('#reason_day').val('');
                $('#reason_selected').val('');
                $('#reason').val('');
                var nym = $(this).attr('name');
                var str = nym.split("x");
                $('#reason_id').val(str[0]);
                $('#reason_day').val(str[1]);
                $('#reason_selected').val($(this).val());
                $('#reason_modal').modal('show');

            });

            $('#reason_modal').on('shown.bs.modal', function (e) {
              $('#reason').focus();
            });

            $('#reason').on('keypress',function(e) {
                if(e.which == 13) {
                    submit_cs();
                }
            });
            function submit_cs(){
                if($('#reason').val().length<=0){
                    alert('You need to enter the reason for change schedule.');
                    return false;
                }
                $.ajax({
                  method: "POST",
                  url: "ajax.php?act=cs_save",
                  data: { shift_id: $('#reason_id').val(), shift: $('#reason_selected').val(), day_name: $('#reason_day').val(), reason: $('#reason').val() }
                })
                .done(function(data){
                    console.log(data);
                    if(data=='0'){
                       alert('Unable to save shift schedules. Please contact ICT dept!');
                    }
                    else{
                        $('#reason_modal').modal('hide');
                    }
                })
            }

            $(".exclude").change(function() {
                if(this.checked) {
                    $(this).closest('tr').addClass('has-error');
                }
            });


            function db_save(field_name,field_val){           
                $.ajax({
                  method: "POST",
                  url: "ajax.php?act=db_save",
                  data: { field_name: field_name, field_val: field_val }
                })
                .done(function(data){
                    console.log(data);
                    if(data=='0'){
                        // alert('Unable to save shift schedules. Please contact ICT dept!');
                    }
                })
            }

           
        </script>
    </body>

</html>
<!-- end scdheduler -->

<!-- ajax.php -->
<?php
include("config.php");
include("functions.php");



if($_GET['act']=='cs_save'){	
	$newshift = num_to_day($_POST['day_name']);
	$s = sqlsrv_fetch_array(sqlsrv_query($conntwo,"select *,CONVERT(varchar(23), effectivityDate, 121) as effectivityDated from zzz_shift_v2 where id='".$_POST['shift_id']."'"));
	$insert = sqlsrv_query($conntwo,"insert into zzz_change_schedule (shift_id,day_name,status,approve_date,file_date,shift,reason,empid,fullname,deptdesc,sectiondesc,old_shift,shift_date) values 
		('".$_POST['shift_id']."','".$newshift."','UNAPPROVED',NULL,'".date('Y-m-d h:i:s')."','".$_POST['shift']."','".$_POST['reason']."',
		'".$s['empid']."','".$s['fullname']."','".$s['deptdesc']."','".$s['sectiondesc']."','".$s[$newshift]."',
		'".date('Y-m-d',strtotime('+'.$_POST['day_name'].' days', strtotime($s['effectivityDated'])))."')");
	if( $insert === false ){
		echo "0";	
	}else{
		echo "1";
	}
	
	echo "insert into zzz_change_schedule (shift_id,day_name,status,approve_date,file_date,shift,reason,empid,fullname,deptdesc,sectiondesc,old_shift,shift_date) values 
	('".$_POST['shift_id']."','".$newshift."','UNAPPROVED',NULL,'".date('Y-m-d h:i:s')."','".$_POST['shift']."','".$_POST['reason']."',
	'".$s['empid']."','".$s['fullname']."','".$s['deptdesc']."','".$s['sectiondesc']."','".$s[$newshift]."',
	'".date('Y-m-d',strtotime('+'.$_POST['day_name'].' days', strtotime($s['effectivityDated'])))."')";
	// '".date('Y-m-d',strtime('+'.$_POST['day_name'].' days', $s['effectivityDate']))."'
}


if($_GET['act']=='db_save'){
	$ex = explode("x", $_POST['field_name']);
	$nym = num_to_day($ex[1]);

	
	$upd = sqlsrv_query($conntwo,"update [zzz_shift_v2],[zzz_shift_group],[JUNDRIE_EmpReports] set $nym='".$_POST['field_val']."' where id='".$ex[0]."'");
	
	if( $upd === false ){
		echo "0";
	}else{
		echo "1";
	}
	// echo "update zzz_shift_v2 set $nym='".$_POST['field_val']."' where id='".$ex[0]."'";
}

<!-- END AJAX.PHP -->
