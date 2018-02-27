<?php

namespace App\Jobs;

use App\Helpers\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet_MemoryDrawing;
use PHPExcel_Writer_Excel2007;
use Illuminate\Support\Facades\Mail;

class CreateReport implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $report_id;
    private $report_detail;
    private $org_schema_detail;
    private $ainfo_detail;
    private $email_list;
    private $dist_ainfo;
    private $ainfo_parsed;

    private $data_start_date_from;
    private $data_start_date_to;
    private $data_end_date_from;
    private $data_end_date_to;
    private $data_start_date_v;
    private $data_end_date_v;

    private $modem_based_modems = array();
    private $modem_based_data = array();

    private $break_based_modems = array();
    private $break_based_group_by = array();

    private $modems_with_problems = array();
    private $mail_detail_info = array();

    //excel specific info
    private $row_cursor = 2;
    private $column_cursor = array();
    private $modem_row_cursor = 2;
    private $modem_column_cursor = array();
    private $break_cons_column_cursor = array();
    private $break_cons_row_cursor = 2;

    public function __construct($id)
    {
        $this->report_id = $id;
    }

    public function handle()
    {
        $report = DB::table('reports as R')
            ->select(
                'R.*',
                'D.logo as distributo_logo',
                'D.id as distributor_id',
                'D.name as distributor_name',
                'U.name as created_by'
            )
            ->leftJoin("distributors as D","D.id","R.distributor_id")
            ->leftJoin("users as U","U.id","R.created_by")
            ->where("R.id",$this->report_id)
            ->where("R.status","<>",0)
            ->first();

        $this->report_detail = json_decode($report->detail,true);
        $this->org_schema_detail = json_decode($report->org_schema_detail,true);
        $this->ainfo_detail = json_decode($report->additional_info,true);
        $this->email_list = json_decode($report->email,true);

        //get all additional info of related distributor
        $dist_ainfo_result = DB::table('additional_infos as A')
            ->select('A.*','P.name as parent_name','P.id as parent_id')
            ->leftJoin('additional_infos as P','A.parent_id','P.id')
            ->where('A.status',1)
            ->where('A.distributor_id',$report->distributor_id)
            //->where('A.parent_id',0)
            ->get();

        $this->dist_ainfo = $dist_ainfo_result;
        $this->AInfoParser();

        if( $this->report_detail["report_type"] == "comparison" ){
            if( $this->report_detail["working_type"] == "instant" ){
                if($this->report_detail["comparison_type"] == "daily"){
                    $this->data_start_date_v = date('d/m/Y',strtotime(str_replace('/', '-', $this->report_detail["comparison_start"])));
                    $this->data_end_date_v =   date('d/m/Y',strtotime(str_replace('/', '-', $this->report_detail["comparison_end"])));

                    $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime(str_replace('/', '-', $this->report_detail["comparison_start"])));
                    $this->data_start_date_to = date('Y-m-d 23:59:59', strtotime(str_replace('/', '-', $this->data_start_date_from)));

                    $this->data_end_date_from = date('Y-m-d 00:00:00', strtotime(str_replace('/', '-', $this->report_detail["comparison_end"])));
                    $this->data_end_date_to = date('Y-m-d 23:59:59', strtotime(str_replace('/', '-', $this->data_end_date_from)));
                }
                else if($this->report_detail["comparison_type"] == "weekly"){
                    $this->data_start_date_v = date('Y/W',strtotime(str_replace('/', '-', $this->report_detail["comparison_start"]))).trans("reporting.week");
                    $this->data_end_date_v =   date('Y/W',strtotime(str_replace('/', '-', $this->report_detail["comparison_end"]))).trans("reporting.week");

                    $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime(str_replace('/', '-', $this->report_detail["comparison_start"])));
                    $this->data_start_date_to = date('Y-m-d 00:00:00', strtotime('+1 week', strtotime($this->data_start_date_from)));

                    $this->data_end_date_from = date('Y-m-d 00:00:00', strtotime(str_replace('/', '-', $this->report_detail["comparison_end"])));
                    $this->data_end_date_to = date('Y-m-d 00:00:00', strtotime('+1 week' , strtotime($this->data_end_date_from)));
                }
                else if($this->report_detail["comparison_type"] == "monthly"){
                    $this->data_start_date_v = date('Y/m',strtotime("01-".str_replace('/','-',$this->report_detail["comparison_start"]))).trans("reporting.month");
                    $this->data_end_date_v =   date('Y/m',strtotime("01-".str_replace('/','-',$this->report_detail["comparison_end"]))).trans("reporting.month");

                    $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime("01-".str_replace('/', '-', $this->report_detail["comparison_start"])));
                    $this->data_start_date_to = date('Y-m-d 00:00:00', strtotime('+1 month', strtotime($this->data_start_date_from)));

                    $this->data_end_date_from = date('Y-m-d 00:00:00', strtotime("01-".str_replace('/', '-', $this->report_detail["comparison_end"])));
                    $this->data_end_date_to = date('Y-m-d 00:00:00', strtotime('+1 month' ,strtotime($this->data_end_date_from)));
                }
                else{
                    $this->data_start_date_v =  $this->report_detail["comparison_start"];
                    $this->data_end_date_v = $this->report_detail["comparison_end"];

                    $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime("01-01-".$this->report_detail["comparison_start"]));
                    $this->data_start_date_to = date('Y-m-d 00:00:00', strtotime('+1 year', strtotime($this->data_start_date_from)));

                    $this->data_end_date_from = date('Y-m-d 00:00:00', strtotime("01-01-".$this->report_detail["comparison_end"]));
                    $this->data_end_date_to = date('Y-m-d 00:00:00', strtotime('+1 year' ,strtotime($this->data_end_date_from)));
                }
            }
            else{ // periodic
                if($this->report_detail["comparison_type"] == "daily"){
                    $comparison_date_type = $this->report_detail["comparison_dates_pd"];

                    $today = date("Y-m-d");

                    // Dün
                    $this->data_end_date_from = date('Y-m-d 00:00:00', strtotime("-1 days", strtotime($today)));
                    $this->data_end_date_to = date('Y-m-d 23:59:59', strtotime($this->data_end_date_from));

                    if($comparison_date_type == "ypdd"){ // Dün ve ondan önceki gün
                        // Ondan önceki gün
                        $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime("-1 days", strtotime($this->data_end_date_from)));
                        $this->data_start_date_to = date('Y-m-d 23:59:59', strtotime($this->data_start_date_from));
                    }
                    else if($comparison_date_type == "ypwd"){ // Dün ve geçen haftaki aynı gün
                        // Geçen haftaki aynı gün
                        $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime("-1 week", strtotime($this->data_end_date_from)));
                        $this->data_start_date_to = date('Y-m-d 23:59:59', strtotime($this->data_start_date_from));
                    }
                    else if($comparison_date_type == "ypmd"){ // Dün ve geçen ayki aynı gün
                        // Geçen ayki aynı gün
                        $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime("-1 month", strtotime($this->data_end_date_from)));
                        $this->data_start_date_to = date('Y-m-d 23:59:59', strtotime($this->data_start_date_from));
                    }
                    else if($comparison_date_type == "ypyd"){ // Dün ve geçen yılki aynı gün
                        // Geçen yılki aynı gün
                        $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime("-1 year", strtotime($this->data_end_date_from)));
                        $this->data_start_date_to = date('Y-m-d 23:59:59', strtotime($this->data_start_date_from));
                    }

                    // verbal date range
                    $this->data_start_date_v = date("d/m/Y", strtotime($this->data_start_date_from));
                    $this->data_end_date_v = date("d/m/Y", strtotime($this->data_end_date_from));
                }
                else if($this->report_detail["comparison_type"] == "weekly"){
                    $comparison_date_type = $this->report_detail["comparison_dates_pw"];

                    $last_monday = date("Y-m-d 00:00:00", strtotime('last Monday'));

                    // Geçen haftanın ilk pazartesisi
                    $this->data_end_date_from = date('Y-m-d 00:00:00', strtotime("-1 week", strtotime($last_monday)));
                    $this->data_end_date_to = $last_monday;

                    // Geçen hafta yılın kaçıncı haftası
                    $week_of_year = date("W", strtotime($this->data_end_date_from));
                    // Geçen hafta içerisinde bulunduğu ayın kaçıncı haftası
                    $first_week_of_this_month = date("W", strtotime(date("Y-m-01", strtotime($this->data_end_date_from))));
                    $week_of_month = $week_of_year - $first_week_of_this_month + 1;

                    if($comparison_date_type == "lwpw"){ // Geçen hafta ve ondan önceki hafta
                        // Ondan önceki hafta
                        $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime("-1 week", strtotime($this->data_end_date_from)));
                        $this->data_start_date_to = $this->data_end_date_from;

                        // verbal date range
                        $this->data_start_date_v = date("Y/W", strtotime($this->data_start_date_from)).trans("reporting.week")." (".date("Y/m/d", strtotime($this->data_start_date_from)).")";
                        $this->data_end_date_v = date("Y/W", strtotime($this->data_end_date_from)).trans("reporting.week")." (".date("Y/m/d", strtotime($this->data_end_date_from)).")";
                    }
                    else if($comparison_date_type == "lwpm"){
                        // geçen ayın ilk günü
                        $first_day_of_previous_month = date('Y-m-01', strtotime("first day of -1 month", strtotime($this->data_end_date_from)) );

                        // geçen ayın x hafta sonraki pazartesisi
                        $monday_x_week_after = date("Y-m-d", strtotime('last Monday', strtotime("+".$week_of_month." week", strtotime($first_day_of_previous_month))));

                        // Geçen ayki aynı haftanın başlangıç ve bitiş günleri
                        $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime($monday_x_week_after));
                        $this->data_start_date_to = date('Y-m-d 00:00:00', strtotime("+1 week", strtotime($this->data_start_date_from)));

                        // verbal date range
                        $this->data_start_date_v = date("Y/m/", strtotime($this->data_start_date_from)).$week_of_month.trans("reporting.week")." (".date("Y/m/d", strtotime($monday_x_week_after)).")";
                        $this->data_end_date_v = date("Y/m/", strtotime($this->data_end_date_from)).$week_of_month.trans("reporting.week")." (".date("Y/m/d", strtotime($this->data_end_date_from)).")";
                    }
                    else if($comparison_date_type == "lwpy"){
                        // Geçen haftanın bulunduğu geçen yılki ayın ilk günü
                        $first_day_of_previous_year_month = date("Y-m-01", strtotime("-1 year", strtotime($this->data_end_date_from)));
                        $monday_x_week_after = date("Y-m-d", strtotime('last Monday', strtotime("+".$week_of_month." week", strtotime($first_day_of_previous_year_month))));

                        $this->data_start_date_from = date('Y-m-d 00:00:00', strtotime($monday_x_week_after));
                        $this->data_start_date_to = date('Y-m-d 00:00:00', strtotime("+1 week", strtotime($this->data_start_date_from)));

                        // verbal date range
                        $this->data_start_date_v = date("Y/m/", strtotime($this->data_start_date_from)).$week_of_month.trans("reporting.week")." (".date("Y/m/d", strtotime($monday_x_week_after)).")";
                        $this->data_end_date_v = date("Y/m/", strtotime($this->data_end_date_from)).$week_of_month.trans("reporting.week")." (".date("Y/m/d", strtotime($this->data_end_date_from)).")";
                    }
                }
                else if($this->report_detail["comparison_type"] == "monthly"){
                    $comparison_date_type = $this->report_detail["comparison_dates_pm"];

                    $this_month = date("Y-m-01 00:00:00");
                    // Previous month
                    $this->data_end_date_from = date("Y-m-01 00:00:00", strtotime("-1 month", strtotime($this_month)));
                    $this->data_end_date_to = $this_month;

                    if($comparison_date_type == "lmpm"){
                        // 2 ay öncesi
                        $this->data_start_date_from = date("Y-m-01 00:00:00", strtotime("-1 month", strtotime($this->data_end_date_from)));
                        $this->data_start_date_to = $this->data_end_date_from;
                    }
                    else if($comparison_date_type == "lmpy"){
                        // Geçen seneki aynı ay
                        $this->data_start_date_from = date("Y-m-01 00:00:00", strtotime("-1 year", strtotime($this->data_end_date_from)));
                        $this->data_start_date_to = date("Y-m-01 00:00:00", strtotime("+1 month", strtotime($this->data_start_date_from)));
                    }

                    // verbal date range
                    $this->data_start_date_v = date("Y/m", strtotime($this->data_start_date_from)).trans("reporting.month");
                    $this->data_end_date_v = date("Y/m", strtotime($this->data_end_date_from)).trans("reporting.month");
                }
                else if($this->report_detail["comparison_type"] == "yearly"){
                    $comparison_date_type = $this->report_detail["comparison_dates_py"];

                    $this_year = date("Y-01-01 00:00:00");
                    // Previous year
                    $this->data_end_date_from = date("Y-01-01 00:00:00", strtotime("-1 year", strtotime($this_year)));
                    $this->data_end_date_to = $this_year;

                    if($comparison_date_type == 'lypy'){
                        // 2 yıl öncesi
                        $this->data_start_date_from = date("Y-01-01 00:00:00", strtotime("-1 year", strtotime($this->data_end_date_from)));
                        $this->data_start_date_to = $this->data_end_date_from;
                    }

                    // verbal date range
                    $this->data_start_date_v = date("Y", strtotime($this->data_start_date_from));
                    $this->data_end_date_v = date("Y", strtotime($this->data_end_date_from));
                }
            }

            $this->createExcel($report);
            $this->sendMail();
        }
        else if($this->report_detail["report_type"] == "stats"){
            //@TODO uncomplete code
            if($this->report_detail["working_type"] == "instant" ){
                $this->data_end_date = $this->report_detail["stats_end"];
                $this->data_start_date = $this->report_detail["stats_start"];
            }
            else if($this->report_detail["working_type"] == "periodic"){
                //@TODO
            }
            $this->createWord($report);
        }
    }

    private function createExcel($report){
        $document_name = str_replace(" ", "_", $report->report_name).'_'.date('dmY-His').'.xlsx';

        // create PHPExcel Object
        $objPHPExcel = new PHPExcel();

        // set document's properties
        $objPHPExcel->getProperties()->setCreator(trans("reporting.report_creator"));
        $objPHPExcel->getProperties()->setLastModifiedBy(trans("reporting.report_creator"));
        $objPHPExcel->getProperties()->setTitle($report->report_name);
        $objPHPExcel->getProperties()->setSubject(trans("reporting.".$this->report_detail["report_type"]."_report"));
        $objPHPExcel->getProperties()->setDescription($report->explanation);

        // prepare to cover page
        $objPHPExcel->setActiveSheetIndex(0);
        $this->prepareExcelCoverpage($objPHPExcel->getActiveSheet(),$report);

        // prepare to content of the report
        $this->prepareExcelContent($report,$objPHPExcel);



        //focus on the first sheet / coverpage before print
        $objPHPExcel->setActiveSheetIndex(0);

        // save created document to storage
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(storage_path().'/app/public/'.$document_name);

        $this->mail_detail_info["attachment_path"] = storage_path().'/app/public/'.$document_name;

        $this->update($report, $document_name);

    }

//######## COVERPAGE ######
    private function prepareExcelCoverpage($coverpage,$report){
        $this->mail_detail_info["report_name"] = $report->report_name;

        $logo = public_path()."/img/avatar/distributor/system.jpg";
        $distributor_name = trans("global.system");

        if($report->distributor_name != "")
            $distributor_name = $report->distributor_name;

        if($report->distributo_logo != "")
            $logo = public_path()."/img/avatar/distributor/".$report->distributo_logo;

        $coverpage->setTitle(trans("reporting.report_definition"));
        $coverpage->mergeCells('A1:I12');

        $coverpage->mergeCells('A16:I18');
        $gdImage = imagecreatefromjpeg($logo);

        $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');
        $objDrawing->setImageResource($gdImage);

        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);

        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setHeight(180);
        $objDrawing->setWidth(180);
        $objDrawing->setCoordinates('D1');
        $objDrawing->setWorksheet($coverpage);

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => [
                'size' => 30
            ]
        );

        $coverpage->getStyle("A16:I18")->applyFromArray($style);
        $coverpage->SetCellValue('A16',trans("global.report_slogan"));

        $coverpage->getColumnDimension('K')->setWidth(23);
        $coverpage->getColumnDimension('L')->setWidth(49);

        $coverpage->SetCellValue('K4',trans("reporting.company"));
        $coverpage->SetCellValue('K5',trans("reporting.created_by"));
        $coverpage->SetCellValue('K6',trans("reporting.created_at"));
        $coverpage->SetCellValue('K8',trans("reporting.report_type"));
        $coverpage->SetCellValue('K9',trans("reporting.working_type"));
        $coverpage->SetCellValue('K10',trans("reporting.comparison_type"));
        $coverpage->SetCellValue('K11',trans("reporting.first_date"));
        $coverpage->SetCellValue('K12',trans("reporting.last_date"));
        $coverpage->SetCellValue('K13',trans("reporting.applied_filters"));

        $coverpage->SetCellValue('L4',$distributor_name);
        $this->mail_detail_info["company"] =array( "title"=>trans("reporting.company"),"value"=>$distributor_name);

        $coverpage->SetCellValue('L5',(($report->created_by==0 || $report->created_by=="" ||
            $report->created_by==NULL)?trans('global.system'):$report->created_by));
        $this->mail_detail_info["created_by"] =array( "title"=>trans("reporting.created_by"),"value"=>(($report->created_by==0 || $report->created_by=="" ||
            $report->created_by==NULL)?trans('global.system'):$report->created_by));

        $coverpage->SetCellValue('L6',date('d/m/Y H:i:s',strtotime($report->created_at)));
        $this->mail_detail_info["created_at"] =array( "title"=>trans("reporting.created_at"),"value"=>date('d/m/Y H:i:s',strtotime($report->created_at)));

        $coverpage->SetCellValue('L8',trans("reporting.".$this->org_schema_detail["filter"]."_comparison"));
        $this->mail_detail_info["report_type"] =array( "title"=>trans("reporting.report_type"),"value"=>trans("reporting.".$this->org_schema_detail["filter"]."_comparison"));

        $coverpage->SetCellValue('L9',trans("reporting.".$this->report_detail["working_type"]));
        $this->mail_detail_info["working_type"] =array( "title"=>trans("reporting.working_type"),"value"=>trans("reporting.".$this->report_detail["working_type"]));


        $coverpage->SetCellValue('L10',trans("reporting.".$this->report_detail["comparison_type"]));
        $this->mail_detail_info["comparison_type"] =array( "title"=>trans("reporting.comparison_type"),"value"=>trans("reporting.".$this->report_detail["comparison_type"]));


        $coverpage->SetCellValue('L11',$this->data_start_date_v);
        $this->mail_detail_info["first_date"] =array( "title"=>trans("reporting.first_date"),"value"=>$this->data_start_date_v);


        $coverpage->SetCellValue('L12',$this->data_end_date_v);
        $this->mail_detail_info["last_date"] =array( "title"=>trans("reporting.last_date"),"value"=>$this->data_end_date_v);


        $coverpage->SetCellValue('L13',$this->ainfo_parsed["filtered_verbal"]);
        $this->mail_detail_info["applied_filters"] =array( "title"=>trans("reporting.applied_filters"),"value"=>$this->ainfo_parsed["filtered_verbal"]);


        $coverpage->getStyle('L13')->getAlignment()->setWrapText(true);

        $coverpage->getStyle("L4:L13")->getFont()->setBold(true);
        $coverpage->getStyle("K4:L13")->applyFromArray(array('alignment'=>array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT,'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER)));


        /*$coverpage->SetCellValue('K15',trans("reporting.note"));
        $coverpage->getStyle("K15:L15")->getFont()->setBold(true);



        $coverpage->getStyle("K16")->applyFromArray(array('font'=>array('color'=>array('rgb'=>'FF0000'))));
        $coverpage->getStyle("K17")->applyFromArray(array('font'=>array('color'=>array('rgb'=>'CCCC00'))));
        $coverpage->SetCellValue('K16',trans("reporting.redExp"));
        $coverpage->SetCellValue('K17',trans("reporting.yellowExp"));

        $coverpage->mergeCells('K15:L15');
        $coverpage->mergeCells('K16:L16');
        $coverpage->mergeCells('K17:L17');*/
    }

    private function prepareExcelContent($report, $objPHPExcel){
        if($this->org_schema_detail["filter"] == "modem_based"){
            //prepare modems info sheet
            $objPHPExcel->createSheet(1);
            $objPHPExcel->setActiveSheetIndex(1);
            $info_sheet = $objPHPExcel->getActiveSheet();
            $info_sheet->setTitle(trans("reporting.modem_info"));

            //prepare modem info headers
            $this->prepareModemInfoHeader($info_sheet);

            $modems = $this->org_schema_detail["values"];

            foreach($modems as $one_modem){
                $this->prepareModemInfo($one_modem,$info_sheet);
            }

            // prepare modems consumption sheet
            $objPHPExcel->createSheet(2);
            $objPHPExcel->setActiveSheetIndex(2);
            $modem_data_sheet = $objPHPExcel->getActiveSheet();
            $modem_data_sheet->setTitle(trans("reporting.consumption_data"));

            $this->prepareModemConsumptionHeader($modem_data_sheet);

            // Is there a modem to be shown?
            if( COUNT($this->modem_based_modems)>0 ){
                foreach ( $this->modem_based_modems as $one_modem ){
                    $this->prepareModemConsumptionData($one_modem);
                }

                $this->calculateModemConsumptionData();
                $this->printModemConsumptionData($modem_data_sheet);

                //handle group by sheets
                $this->prepareBreakGroupByData("modem");
                $this->printBreakGroupBy($objPHPExcel, 3);
            }
        }
        else if($this->org_schema_detail["filter"] == "break_based"){
            //prepare break info sheet
            $objPHPExcel->createSheet(1);
            $objPHPExcel->setActiveSheetIndex(1);
            $info_sheet = $objPHPExcel->getActiveSheet();
            $info_sheet->setTitle(trans("reporting.break_info"));

            //prepare Break info headers
            $this->prepareBreakInfoHeader($info_sheet);

            $breaks = $this->org_schema_detail["values"];

            foreach($breaks as $one_break){
                $this->prepareBreakInfo($one_break,$info_sheet);
            }

            $this->calculateBreakData();

            $this->printBreakConsumptionData($objPHPExcel);

            //handle group by sheets
            $this->prepareBreakGroupByData("break");
            $this->printBreakGroupBy($objPHPExcel, 4);
        }
    }

//####### MODEM BASED FUNCTIONS #######
    private function prepareModemInfoHeader($sheet){
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => [
                'size' => 12,
                'bold' => true
            ],
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $sheet->SetCellValue('A1',trans("reporting.modem_no"));
        $sheet->SetCellValue('B1',trans("reporting.location"));
        $sheet->SetCellValue('C1',trans("reporting.client_name"));
        $sheet->SetCellValue('D1',trans("reporting.client_authorized_name"));
        $sheet->SetCellValue('E1',trans("reporting.organization"));
        $sheet->SetCellValue('F1',trans("reporting.authorized_name"));

        $this->column_cursor["modem_no"] = "A";
        $this->column_cursor["location"] = "B";
        $this->column_cursor["client_name"] = "C";
        $this->column_cursor["client_authorized_name"] = "D";
        $this->column_cursor["organization"] = "E";
        $this->column_cursor["authorized_name"] = "F";

        $column = 5;
        foreach($this->dist_ainfo as $one_info){
            if($one_info->parent_id == 0){
                $sheet->setCellValueByColumnAndRow(++$column,1,$one_info->name);
                $this->column_cursor[$one_info->id] = PHPExcel_Cell::stringFromColumnIndex($column);
            }
        }

        $sheet->getStyle("A1:".PHPExcel_Cell::stringFromColumnIndex($column)."1")->applyFromArray($style);

        foreach(range('A',PHPExcel_Cell::stringFromColumnIndex($column)) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    private function prepareModemInfo($id,$sheet){
        $result = DB::table('modems as M')
            ->select(
                'M.*',
                'C.name as client_name',
                'C.authorized_name as authorized_name',
                'C.org_schema_id as org_schema_id',
                'C.distributor_id as distributor_id',
                DB::raw("JSON_UNQUOTE(json_extract(M.location, '$.verbal')) as location_verbal")
            )
            ->leftJoin('clients as C','C.id','M.client_id')
            ->where('M.id',$id)
            ->where('M.status','<>',0)
            ->first();

        //check if this modem is filtered by addtional info
        if(COUNT($this->ainfo_parsed["filtered_value"])>0){
            if($result->additional_info == ""){
                return;
            }
            else{
                $modem_ainfo = json_decode($result->additional_info,true);

                foreach ($this->ainfo_parsed["filtered_value"] as $one_parsed){
                    $is_hit = false;

                    foreach ($modem_ainfo as $one_modem_info){
                        if($one_parsed["id"] == $one_modem_info["id"]){
                            if($one_parsed["value"] != $one_modem_info["value"]){
                                return;
                            }

                            $is_hit = true;
                        }
                    }

                    if($is_hit == false)
                        return;
                }
            }
        }

        $this->modem_based_modems[$result->id] = array(
            "modem_id" => $result->id,
            "modem_no" => $result->serial_no,
            "location" => $result->location_verbal,
            "airport_id" => $result->airport_id,
            "client_name" => $result->client_name,
            "add_info" => $this->breakAdditionalInfo($result->additional_info)
        );

        $sheet->SetCellValue($this->column_cursor["modem_no"].$this->row_cursor,$result->serial_no);
        $sheet->SetCellValue($this->column_cursor["location"].$this->row_cursor,$result->location_verbal);
        $sheet->SetCellValue($this->column_cursor["client_name"].$this->row_cursor,$result->client_name);
        $sheet->SetCellValue($this->column_cursor["client_authorized_name"].$this->row_cursor,$result->authorized_name);

        //handle org info
        $all_structure = DB::table('organization_schema as O')
            ->select(
                'O.*',
                DB::raw("JSON_UNQUOTE(json_extract(O.info, '$.authorized_person')) as authorized_person")
                )
            ->where('distributor_id',$result->distributor_id)
            ->where('status','<>',0)
            ->get();

        $sheet->SetCellValue($this->column_cursor["organization"].$this->row_cursor,trim($this->getOrgPath($all_structure,$result->org_schema_id),'/'));

        foreach($all_structure as $one_node){
            if($one_node->id == $result->org_schema_id){
                $sheet->SetCellValue($this->column_cursor["authorized_name"].$this->row_cursor,$one_node->authorized_person);
                break;
            }
        }

        //handle additional_info
        $add_info_ids = "";
        $add_info = json_decode($result->additional_info,true);
        if(COUNT($add_info)>0 && is_numeric($add_info[0]["id"])){

            foreach($add_info as $one_info){
                $add_info_ids .= $one_info["id"].",";
            }

            $add_info_ids = trim($add_info_ids,',');

            $a_info = DB::table("additional_infos")
                    ->whereRaw("FIND_IN_SET(id,'".$add_info_ids."')>0 OR FIND_IN_SET(parent_id,'".$add_info_ids."')>0")
                    ->get();

            foreach($add_info as $one_info){
                foreach($a_info as $one_a_info){
                    if($one_info["id"]==$one_a_info->id){
                        if($one_a_info->is_category == 1){
                            foreach($a_info as $c_a_info){
                                if($c_a_info->id."" == "".$one_info["value"]){
                                    $sheet->SetCellValue($this->column_cursor[$one_info["id"]].$this->row_cursor,$c_a_info->name);
                                    break;
                                }
                            }
                        }
                        else{
                            $sheet->SetCellValue($this->column_cursor[$one_info["id"]].$this->row_cursor,$one_info["value"]);
                            $this->modem_based_modems[$result->id][$one_info["id"]] = $one_info["value"];
                        }

                        break;
                    }
                }
            }
        }
        $this->row_cursor++;
    }

    private function prepareModemConsumptionHeader($sheet){
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'font' => [
                'size' => 12,
                'bold' => true
            ],
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $sheet->SetCellValue('A1',trans("reporting.modem_no"));
        $this->modem_column_cursor["modem_no"] = "A";

        $sheet->SetCellValue('B1',trans("reporting.location"));
        $this->modem_column_cursor["location"] = "B";

        $sheet->SetCellValue('C1',trans("reporting.client_name"));
        $this->modem_column_cursor["client_name"] = "C";

        $sheet->SetCellValue('D1',trans("reporting.tcf"));
        $this->modem_column_cursor["tcf"] = "D";

        $sheet->SetCellValue('E1',trans("reporting.tcl"));
        $this->modem_column_cursor["tcl"] = "E";

        $sheet->SetCellValue('F1',trans("reporting.tccr"));
        $this->modem_column_cursor["tccr"] = "F";

        $sheet->SetCellValue('G1',trans("reporting.t1f"));
        $this->modem_column_cursor["t1f"] = "G";

        $sheet->SetCellValue('H1',trans("reporting.t1l"));
        $this->modem_column_cursor["t1l"] = "H";

        $sheet->SetCellValue('I1',trans("reporting.t2f"));
        $this->modem_column_cursor["t2f"] = "I";

        $sheet->SetCellValue('J1',trans("reporting.t2l"));
        $this->modem_column_cursor["t2l"] = "J";

        $sheet->SetCellValue('K1',trans("reporting.t3f"));
        $this->modem_column_cursor["t3f"] = "K";

        $sheet->SetCellValue('L1',trans("reporting.t3l"));
        $this->modem_column_cursor["t3l"] = "L";

        $sheet->SetCellValue('M1',trans("reporting.t3f")."/".trans("reporting.tcf"));
        $this->modem_column_cursor["t3f_tcf"] = "M";

        $sheet->SetCellValue('N1',trans("reporting.t3l")."/".trans("reporting.tcl"));
        $this->modem_column_cursor["t3l_tcl"] = "N";

        $sheet->SetCellValue('O1',trans("reporting.std")." (".trans("reporting.tccr").")");
        $this->modem_column_cursor["std_tccr"] = "O";

        $sheet->SetCellValue('P1',trans('reporting.average') . "(" . trans("reporting.cr").") + 2std");
        $this->modem_column_cursor["tccr_pstd"] = "P";

        $sheet->SetCellValue('Q1',trans('reporting.average') . "(" . trans("reporting.cr").") - 2std");
        $this->modem_column_cursor["tccr_mstd"] = "Q";

        $sheet->SetCellValue('R1',trans("reporting.std")." (".trans("reporting.t3l")."/".trans("reporting.tcl").")");
        $this->modem_column_cursor["std_t3l_tcl"] = "R";

        $sheet->SetCellValue('S1',trans('reporting.average') ."(".trans("reporting.t3l")."/".trans("reporting.tcl").")"." + 2std");
        $this->modem_column_cursor["t3l_tcl_pstd"] = "S";

        $sheet->SetCellValue('T1',trans('reporting.average') ."(".trans("reporting.t3l")."/".trans("reporting.tcl").")"." - 2std");
        $this->modem_column_cursor["t3l_tcl_mstd"] = "T";

        $sheet->SetCellValue('U1',trans("reporting.taf"));
        $this->modem_column_cursor["taf"] = "U";

        $sheet->SetCellValue('V1',trans("reporting.tal"));
        $this->modem_column_cursor["tal"] = "V";

        $column = 21;
        foreach($this->ainfo_parsed["analyzed"] as $one_info){
            $sheet->setCellValueByColumnAndRow(++$column, 1, trans("reporting.tcl")."/".$one_info["name"]);
            $tempLetter = PHPExcel_Cell::stringFromColumnIndex($column);
            $this->modem_column_cursor[$one_info["id"]] = PHPExcel_Cell::stringFromColumnIndex($column);

            $sheet->setCellValueByColumnAndRow(++$column, 1, trans("reporting.std")." (".$tempLetter.")");
            $this->modem_column_cursor[$one_info["id"]."_std"] = PHPExcel_Cell::stringFromColumnIndex($column);

            $sheet->setCellValueByColumnAndRow(++$column, 1, trans('reporting.average') . "(" . $tempLetter.") + 2std");
            $this->modem_column_cursor[$one_info["id"]."_pstd"] = PHPExcel_Cell::stringFromColumnIndex($column);

            $sheet->setCellValueByColumnAndRow(++$column, 1, trans('reporting.average') . "(" . $tempLetter.") - 2std");
            $this->modem_column_cursor[$one_info["id"]."_mstd"] = PHPExcel_Cell::stringFromColumnIndex($column);
        }

        $sheet->getStyle("A1:".PHPExcel_Cell::stringFromColumnIndex($column)."1")->applyFromArray($style);

        $sheet->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D:'.PHPExcel_Cell::stringFromColumnIndex($column))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        for($i=0; $i<=$column; $i++){
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    private function prepareModemConsumptionData($modem, $is_break = false){
        $device = DB::table('devices as D')
            ->select(
                'D.id as device_id',
                'D.device_no as device_no',
                'DT.type as device_type',
                'D.multiplier as multiplier'
            )
            ->leftJoin('device_type as DT', 'DT.id', 'D.device_type_id')
            ->where('D.modem_id', $modem["modem_id"])
            ->where('D.status', '<>', 0)
            ->first();

        $taf = DB::table("temperature as T")
            ->select(
                DB::raw('
                    AVG(T.mean_temperature) as taf                  
                ')
            )
            ->where('T.airport_id', $modem["airport_id"])
            ->whereBetween('T.date', [$this->data_start_date_from, $this->data_start_date_to])
            ->first();

        $tal = DB::table("temperature as T")
            ->select(
                DB::raw('
                    AVG(T.mean_temperature) as tal                  
                ')
            )
            ->where('T.airport_id', $modem["airport_id"])
            ->whereBetween('T.date', [$this->data_end_date_from, $this->data_end_date_to])
            ->first();

        if( !(COUNT($device) > 0 && is_numeric($device->device_id)) ){
            return;
        }

        $table_name = "device_records_modbus";
        if( $device->device_type == "meter" ){
            $table_name = "device_records_meter";
        }

        $first_data = DB::table($table_name . " as DRM")
            ->select(
                DB::raw('
                    (MAX(positive_active_energy_total) - MIN(positive_active_energy_total)) as total,
                    (MAX(positive_active_energy_t1) - MIN(positive_active_energy_t1)) as t1,
                    (MAX(positive_active_energy_t2) - MIN(positive_active_energy_t2)) as t2,
                    (MAX(positive_active_energy_t3) - MIN(positive_active_energy_t3)) as t3                    
                ')
            )
            ->where('DRM.device_id', $device->device_id)
            ->whereBetween('DRM.server_timestamp', [$this->data_start_date_from, $this->data_start_date_to])
            ->first();

        $last_data = DB::table($table_name . " as DRM")
            ->select(
                DB::raw('
                    (MAX(positive_active_energy_total) - MIN(positive_active_energy_total)) as total,
                    (MAX(positive_active_energy_t1) - MIN(positive_active_energy_t1)) as t1,
                    (MAX(positive_active_energy_t2) - MIN(positive_active_energy_t2)) as t2,
                    (MAX(positive_active_energy_t3) - MIN(positive_active_energy_t3)) as t3                    
                ')
            )
            ->where('DRM.device_id', $device->device_id)
            ->whereBetween('DRM.server_timestamp', [$this->data_end_date_from, $this->data_end_date_to])
            ->first();

        if($is_break == false){
            $this->modem_based_data["modems"][$modem["modem_id"]] = array(
                "modem" => $modem,
                "tcf" => $first_data->total * $device->multiplier,
                "tcl" => $last_data->total * $device->multiplier,
                "t1f" => $first_data->t1 * $device->multiplier,
                "t1l" => $last_data->t1 * $device->multiplier,
                "t2f" => $first_data->t2 * $device->multiplier,
                "t2l" => $last_data->t2 * $device->multiplier,
                "t3f" => $first_data->t3 * $device->multiplier,
                "t3l" => $last_data->t3 * $device->multiplier,
                "tccr" => $first_data->total!=0 ? (($last_data->total - $first_data->total)/$first_data->total):$last_data->total,
                "t3f_tcf" => $first_data->total!=0 ? ($first_data->t3/$first_data->total):0,
                "t3l_tcl" => $last_data->total!=0 ? ($last_data->t3/$last_data->total):0,
                "ainfo" => array(),
                "add_info" =>array(),
                "taf" => $taf->taf,
                "tal" => $tal->tal
            );
        }
        else{
            return array(
                "tcf" => $first_data->total * $device->multiplier,
                "tcl" => $last_data->total * $device->multiplier,
                "t1f" => $first_data->t1 * $device->multiplier,
                "t1l" => $last_data->t1 * $device->multiplier,
                "t2f" => $first_data->t2 * $device->multiplier,
                "t2l" => $last_data->t2 * $device->multiplier,
                "t3f" => $first_data->t3 * $device->multiplier,
                "t3l" => $last_data->t3 * $device->multiplier,
                "tccr" => $first_data->total!=0 ? (($last_data->total - $first_data->total)/$first_data->total):$last_data->total,
                "t3f_tcf" => $first_data->total!=0 ? ($first_data->t3/$first_data->total):0,
                "t3l_tcl" => $last_data->total!=0 ? ($last_data->t3/$last_data->total):0,
                "ainfo" => array(),
                "taf" => $taf->taf,
                "tal" => $tal->tal
            );
        }
    }

    private function calculateModemConsumptionData(){
        $ccr_std_array = array();
        $total_all_consumption_first = 0;
        $total_all_consumption_last = 0;

        $t3l_tcl_std_array = array();
        $total_all_t3_last = 0;

        $ainfo_calculation = array();

        foreach ($this->modem_based_data["modems"] as &$one_modem_data) {
            $ccr_std_array[] = $one_modem_data["tccr"];
            $total_all_consumption_first +=  $one_modem_data["tcf"];
            $total_all_consumption_last +=  $one_modem_data["tcl"];

            $t3l_tcl_std_array[] = $one_modem_data["t3l_tcl"];
            $total_all_t3_last += $one_modem_data["t3l"];

            foreach ($this->ainfo_parsed["analyzed"] as $one_analyzed){
                if(isset($one_modem_data["modem"][$one_analyzed["id"]]) && is_numeric($one_modem_data["modem"][$one_analyzed["id"]]) && $one_modem_data["modem"][$one_analyzed["id"]] != 0){

                    $one_modem_data["ainfo"][$one_analyzed["id"]] = $one_modem_data["tcl"] / $one_modem_data["modem"][$one_analyzed["id"]];

                    $ainfo_calculation[$one_analyzed["id"]]["std_array"][] = $one_modem_data["ainfo"][$one_analyzed["id"]];
                    $ainfo_calculation[$one_analyzed["id"]]["tcl"] = isset($ainfo_calculation[$one_analyzed["id"]]["tcl"])?($ainfo_calculation[$one_analyzed["id"]]["tcl"] + $one_modem_data["tcl"]):$one_modem_data["tcl"];
                    $ainfo_calculation[$one_analyzed["id"]]["tv"] = isset($ainfo_calculation[$one_analyzed["id"]]["tv"])?($ainfo_calculation[$one_analyzed["id"]]["tv"]+$one_modem_data["modem"][$one_analyzed["id"]]):$one_modem_data["modem"][$one_analyzed["id"]];
                }
            }
        }

        $this->modem_based_data["global"]["std_tccr"] = Helper::stats_standard_deviation($ccr_std_array);
        $this->modem_based_data["global"]["total_all_consumption_first"] = $total_all_consumption_first;
        $this->modem_based_data["global"]["total_all_consumption_last"] = $total_all_consumption_last;
        $this->modem_based_data["global"]["avarege_tccr"] = $total_all_consumption_first!=0 ? (($total_all_consumption_last - $total_all_consumption_first)/$total_all_consumption_first):$total_all_consumption_last;
        $this->modem_based_data["global"]["acr_pstd"] = $this->modem_based_data["global"]["avarege_tccr"] + 2*$this->modem_based_data["global"]["std_tccr"];
        $this->modem_based_data["global"]["acr_mstd"] = $this->modem_based_data["global"]["avarege_tccr"] - 2*$this->modem_based_data["global"]["std_tccr"];

        $this->modem_based_data["global"]["std_t3l_tcl"] = Helper::stats_standard_deviation($t3l_tcl_std_array);
        $this->modem_based_data["global"]["avarage_t3l_tcl"] = $total_all_consumption_last !=0 ? ($total_all_t3_last/$total_all_consumption_last):0;
        $this->modem_based_data["global"]["t3l_tcl_pstd"] = $this->modem_based_data["global"]["avarage_t3l_tcl"] + 2*$this->modem_based_data["global"]["std_t3l_tcl"];
        $this->modem_based_data["global"]["t3l_tcl_mstd"] = $this->modem_based_data["global"]["avarage_t3l_tcl"] - 2*$this->modem_based_data["global"]["std_t3l_tcl"];

        foreach ($ainfo_calculation as &$one_calculation){
            $one_calculation["std"] = Helper::stats_standard_deviation($one_calculation["std_array"]);
            unset($one_calculation["std_array"]);

            $one_calculation["avarage"] = $one_calculation["tcl"]/$one_calculation["tv"];
            $one_calculation["avarage_pstd"] = $one_calculation["avarage"] + 2*$one_calculation["std"];
            $one_calculation["avarage_mstd"] = $one_calculation["avarage"] - 2*$one_calculation["std"];

            unset($one_calculation["tcl"]);
            unset($one_calculation["tv"]);
        }

        $this->modem_based_data["global"]["ainfo"] = $ainfo_calculation;
    }

    private function printModemConsumptionData($sheet){
        if(!isset($this->modem_based_data["modems"]))
            return;

        //print_r($this->modem_based_data);
        //sort modem based data by considering the status of modems meaning if they are out of boundaries or not
        uasort($this->modem_based_data["modems"], function(&$a,&$b){
            $a_has_problem = false;
            $b_has_problem = false;

            //check for consumption
            if($a["tccr"] < $this->modem_based_data["global"]["acr_mstd"] ||
                $a["tccr"]>$this->modem_based_data["global"]["acr_pstd"]){

                $a_has_problem = true;
            }

            if($b["tccr"] < $this->modem_based_data["global"]["acr_mstd"] ||
                $b["tccr"]>$this->modem_based_data["global"]["acr_pstd"]){

                $b_has_problem = true;
            }

            //check for additional infos
            foreach($this->modem_based_data["global"]["ainfo"] as $key=>$one_info){
                if(isset($a["ainfo"][$key])){
                    if($a["ainfo"][$key] < $one_info["avarage_mstd"] || $a["ainfo"][$key] > $one_info["avarage_pstd"]){
                        $a_has_problem = true;
                    }
                }
                if(isset($b["ainfo"][$key])){
                    if($b["ainfo"][$key] < $one_info["avarage_mstd"] || $b["ainfo"][$key] > $one_info["avarage_pstd"]){
                        $b_has_problem = true;
                    }
                }
            }

            if($a_has_problem == true){
                $this->modems_with_problems[$a["modem"]["modem_id"]] = $a["modem"]["modem_id"];
                return -1;
            }
            else if($b_has_problem){
                $this->modems_with_problems[$b["modem"]["modem_id"]] = $b["modem"]["modem_id"];
                return 1;
            }
            else {
                return 1;
            }
        });

        foreach ($this->modem_based_data["modems"] as $one_modem_data){
            $sheet->SetCellValue($this->modem_column_cursor["modem_no"].$this->modem_row_cursor, $one_modem_data["modem"]["modem_no"]);
            $sheet->SetCellValue($this->modem_column_cursor["location"].$this->modem_row_cursor, $one_modem_data["modem"]["location"]);
            $sheet->SetCellValue($this->modem_column_cursor["client_name"].$this->modem_row_cursor, $one_modem_data["modem"]["client_name"]);

            $sheet->SetCellValue($this->modem_column_cursor["tcf"].$this->modem_row_cursor, number_format($one_modem_data["tcf"],2));
            $sheet->SetCellValue($this->modem_column_cursor["tcl"].$this->modem_row_cursor, number_format($one_modem_data["tcl"],2));
            $sheet->SetCellValue($this->modem_column_cursor["tccr"].$this->modem_row_cursor, number_format($one_modem_data["tccr"],2));

            $sheet->SetCellValue($this->modem_column_cursor["t1f"].$this->modem_row_cursor, number_format($one_modem_data["t1f"],2));
            $sheet->SetCellValue($this->modem_column_cursor["t1l"].$this->modem_row_cursor, number_format($one_modem_data["t1l"],2));

            $sheet->SetCellValue($this->modem_column_cursor["t2f"].$this->modem_row_cursor, number_format($one_modem_data["t2f"],2));
            $sheet->SetCellValue($this->modem_column_cursor["t2l"].$this->modem_row_cursor, number_format($one_modem_data["t2l"],2));

            $sheet->SetCellValue($this->modem_column_cursor["t3f"].$this->modem_row_cursor, number_format($one_modem_data["t3f"],2));
            $sheet->SetCellValue($this->modem_column_cursor["t3l"].$this->modem_row_cursor, number_format($one_modem_data["t3l"],2));

            $sheet->SetCellValue($this->modem_column_cursor["t3f_tcf"].$this->modem_row_cursor, number_format($one_modem_data["t3f_tcf"],2));
            $sheet->SetCellValue($this->modem_column_cursor["t3l_tcl"].$this->modem_row_cursor, number_format($one_modem_data["t3l_tcl"],2));

            $sheet->SetCellValue($this->modem_column_cursor["std_tccr"].$this->modem_row_cursor, number_format($this->modem_based_data["global"]["std_tccr"],2));
            $sheet->SetCellValue($this->modem_column_cursor["tccr_pstd"].$this->modem_row_cursor, number_format($this->modem_based_data["global"]["acr_pstd"],2));
            $sheet->SetCellValue($this->modem_column_cursor["tccr_mstd"].$this->modem_row_cursor, number_format($this->modem_based_data["global"]["acr_mstd"],2));

            $sheet->SetCellValue($this->modem_column_cursor["std_t3l_tcl"].$this->modem_row_cursor, number_format($this->modem_based_data["global"]["std_t3l_tcl"],2));
            $sheet->SetCellValue($this->modem_column_cursor["t3l_tcl_pstd"].$this->modem_row_cursor, number_format($this->modem_based_data["global"]["t3l_tcl_pstd"],2));
            $sheet->SetCellValue($this->modem_column_cursor["t3l_tcl_mstd"].$this->modem_row_cursor, number_format($this->modem_based_data["global"]["t3l_tcl_mstd"],2));


            $sheet->SetCellValue($this->modem_column_cursor["taf"].$this->modem_row_cursor, number_format($one_modem_data["taf"],2));
            $sheet->SetCellValue($this->modem_column_cursor["tal"].$this->modem_row_cursor, number_format($one_modem_data["tal"],2));

            foreach($one_modem_data["ainfo"] as $key=>$one_ainfo){
                $sheet->SetCellValue($this->modem_column_cursor[$key].$this->modem_row_cursor, number_format($one_ainfo,2));
            }

            $last_column = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString($this->modem_column_cursor["t3l_tcl_mstd"])+1);

            foreach($this->modem_based_data["global"]["ainfo"] as $key=>$one_info){

                $sheet->SetCellValue($this->modem_column_cursor[$key."_std"].$this->modem_row_cursor, number_format($one_info["std"],2));
                $sheet->SetCellValue($this->modem_column_cursor[$key."_pstd"].$this->modem_row_cursor, number_format($one_info["avarage_pstd"],2));
                $sheet->SetCellValue($this->modem_column_cursor[$key."_mstd"].$this->modem_row_cursor, number_format($one_info["avarage_mstd"],2));

                $last_column = $this->modem_column_cursor[$key."_mstd"];

            }

            //check if this out of boundaries
            if(isset($this->modems_with_problems[$one_modem_data["modem"]["modem_id"]]) == true)
                $sheet->getStyle('A'.$this->modem_row_cursor.':'.$last_column.$this->modem_row_cursor)
                    ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'E05CC2')
                            )
                        )
                    );

            $this->modem_row_cursor++;
        }
    }


//####### BREAK BASED FUNCTIONS #######

    private function prepareBreakInfoHeader($sheet){
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font' => [
                'size' => 12,
                'bold' => true
            ],
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $sheet->SetCellValue('A1',trans("reporting.break_name"));
        $sheet->SetCellValue('B1',trans("reporting.client_authorized_name"));
        $sheet->SetCellValue('C1',trans("reporting.organization"));
        $sheet->SetCellValue('D1',trans("reporting.active_modem_count"));

        $this->column_cursor["break_name"] = "A";
        $this->column_cursor["authorized_name"] = "B";
        $this->column_cursor["organization"] = "C";
        $this->column_cursor["modem_count"] = "D";

        $sheet->getStyle("A1:D1")->applyFromArray($style);
        foreach(range('A','D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

    }

    private function prepareBreakInfo($id,$sheet){
        $result = DB::table('organization_schema')
            ->select(
                'id',
                'name',
                DB::raw("JSON_UNQUOTE(json_extract(info, '$.authorized_person')) as authorized_person"),
                'distributor_id'
            )
            ->where('status','<>',0)
            ->where('id',$id)
            ->first();

        //handle org info
        $all_structure = DB::table('organization_schema as O')
            ->where('distributor_id',$result->distributor_id)
            ->where('status','<>',0)
            ->get();
        $organization = trim($this->getOrgPath($all_structure,$id),'/');

        //handle modem count
        $modem_count = 0;
        $parent_list = implode(',',$this->getParentList($all_structure,$id));

        $modems = DB::table('modems as M')
            ->select(
                'M.*',
                'C.name as client_name',
                DB::raw("JSON_UNQUOTE(json_extract(M.location, '$.verbal')) as location_verbal")
            )
            ->join('clients as C','C.id','M.client_id')
            ->whereRaw("FIND_IN_SET(C.org_schema_id,'".$parent_list."')>0 AND M.status<>0 AND C.status<>0")
            ->get();


        //check if this modem is filtered by addtional info
        if(COUNT($modems)>0 && is_numeric($modems[0]->id)){
            foreach ($modems as $one_modem){

                $devices = DB::table('devices')
                    ->where('modem_id', $one_modem->id)
                    ->where('status', '<>', 0)
                    ->get();

                if( !(COUNT($devices) > 0 && isset($devices[0]->id)) ){
                    continue;
                }

                if(COUNT($this->ainfo_parsed["filtered_value"])>0){
                    if($one_modem->additional_info == ""){
                        continue;
                    }
                    else{
                        $modem_ainfo = json_decode($one_modem->additional_info,true);

                        $global_is_hit = true;

                        foreach ($this->ainfo_parsed["filtered_value"] as $one_parsed){
                            $is_hit = false;

                            foreach ($modem_ainfo as $one_modem_info){
                                if($one_parsed["id"] == $one_modem_info["id"]){
                                    if($one_parsed["value"] != $one_modem_info["value"]){
                                        $is_hit = false;
                                        break;
                                    }
                                    else{
                                        $is_hit = true;
                                        break;
                                    }
                                }
                            }

                            if($is_hit == false){
                                $global_is_hit = false;
                                break;
                            }
                        }

                        if($global_is_hit == true){
                            $this->break_based_modems[$id]["modems"][$one_modem->id] = array(
                                "modem_id" => $one_modem->id,
                                "modem_no" => $one_modem->serial_no,
                                "location" => $one_modem->location_verbal,
                                "airport_id" => $one_modem->airport_id,
                                "client_name" => $one_modem->client_name
                            );

                            $this->break_based_modems[$id]["modems"][$one_modem->id]["consumption"] =
                                $this->prepareModemConsumptionData($this->break_based_modems[$id]["modems"][$one_modem->id],true);

                            $this->break_based_modems[$id]["modems"][$one_modem->id]["add_info"] =
                                $this->breakAdditionalInfo($one_modem->additional_info);
                            $modem_count++;
                        }
                    }
                }
                else{
                    $this->break_based_modems[$id]["modems"][$one_modem->id] = array(
                        "modem_id" => $one_modem->id,
                        "modem_no" => $one_modem->serial_no,
                        "location" => $one_modem->location_verbal,
                        "airport_id" => $one_modem->airport_id,
                        "client_name" => $one_modem->client_name
                    );

                    $this->break_based_modems[$id]["modems"][$one_modem->id]["consumption"] =
                        $this->prepareModemConsumptionData($this->break_based_modems[$id]["modems"][$one_modem->id],true);

                    $this->break_based_modems[$id]["modems"][$one_modem->id]["add_info"] =
                        $this->breakAdditionalInfo($one_modem->additional_info);

                    $modem_count++;
                }
            }
        }

        if(isset($result->id) && is_numeric($result->id)){
            $this->break_based_modems[$id]["description"]["break_name"] = $result->name;
            $this->break_based_modems[$id]["description"]["authorized_name"] = $result->authorized_person;
            $this->break_based_modems[$id]["description"]["organization"] = $organization;
            $this->break_based_modems[$id]["description"]["modem_count"] = $modem_count;

            $sheet->SetCellValue($this->column_cursor["break_name"].$this->row_cursor,$result->name);
            $sheet->SetCellValue($this->column_cursor["authorized_name"].$this->row_cursor,$result->authorized_person);
            $sheet->SetCellValue($this->column_cursor["organization"].$this->row_cursor,$organization);
            $sheet->SetCellValue($this->column_cursor["modem_count"].$this->row_cursor,$modem_count);
        }

        $this->row_cursor++;
    }

    private function calculateBreakData(){

        foreach ($this->break_based_modems as $key=>&$one_break){

            $ccr_std_array = array();
            $total_all_consumption_first = 0;
            $total_all_consumption_last = 0;

            $total_t1_first = 0;
            $total_t1_last = 0;

            $total_t2_first = 0;
            $total_t2_last = 0;

            $total_t3_first = 0;
            $total_t3_last = 0;

            $t3l_tcl_std_array = array();

            $ainfo_calculation = array();

            $min_change_ratio = false;
            $max_change_ratio = false;

            $min_t3l_tcl = false;
            $max_t3l_tcl = false;

            if(!isset($one_break["modems"]))
                continue;

            foreach($one_break["modems"] as &$one_modem){

                $ccr_std_array[] = $one_modem["consumption"]["tccr"];
                $total_all_consumption_first +=  $one_modem["consumption"]["tcf"];
                $total_all_consumption_last +=  $one_modem["consumption"]["tcl"];

                $total_t1_first += $one_modem["consumption"]["t1f"];
                $total_t1_last += $one_modem["consumption"]["t1l"];

                $total_t2_first += $one_modem["consumption"]["t2f"];
                $total_t2_last += $one_modem["consumption"]["t2l"];

                $total_t3_first += $one_modem["consumption"]["t3f"];
                $total_t3_last += $one_modem["consumption"]["t3l"];

                $t3l_tcl_std_array[] = $one_modem["consumption"]["t3l_tcl"];


                if($min_change_ratio == false)
                    $min_change_ratio = $one_modem["consumption"]["tccr"];
                else{
                    if($one_modem["consumption"]["tccr"] < $min_change_ratio)
                        $min_change_ratio = $one_modem["consumption"]["tccr"];
                }

                if($max_change_ratio == false)
                    $max_change_ratio = $one_modem["consumption"]["tccr"];
                else{
                    if($one_modem["consumption"]["tccr"] > $max_change_ratio)
                        $max_change_ratio = $one_modem["consumption"]["tccr"];
                }


                if($min_t3l_tcl == false)
                    $min_t3l_tcl = $one_modem["consumption"]["t3l_tcl"];
                else{
                    if($one_modem["consumption"]["t3l_tcl"] < $min_t3l_tcl)
                        $min_t3l_tcl = $one_modem["consumption"]["t3l_tcl"];
                }

                if($max_t3l_tcl == false)
                    $max_t3l_tcl = $one_modem["consumption"]["t3l_tcl"];
                else{
                    if($one_modem["consumption"]["t3l_tcl"] > $max_t3l_tcl)
                        $max_t3l_tcl = $one_modem["consumption"]["t3l_tcl"];
                }

                foreach ($this->ainfo_parsed["analyzed"] as $one_analyzed){

                    if(isset($one_modem["add_info"][$one_analyzed["id"]]) && is_numeric
                        ($one_modem["add_info"][$one_analyzed["id"]]["value"]) &&
                        $one_modem["add_info"][$one_analyzed["id"]]["value"]
                        != 0){

                        $one_modem["add_info"][$one_analyzed["id"]]["ratio"] = $one_modem["consumption"]["tcl"] / $one_modem["add_info"][$one_analyzed["id"]]["value"];

                        $ainfo_calculation[$one_analyzed["id"]]["std_array"][] = $one_modem["add_info"][$one_analyzed["id"]]["ratio"];

                        $ainfo_calculation[$one_analyzed["id"]]["tcl"] = isset($ainfo_calculation[$one_analyzed["id"]]["tcl"])?($ainfo_calculation[$one_analyzed["id"]]["tcl"] + $one_modem["consumption"]["tcl"]):$one_modem["consumption"]["tcl"];

                        $ainfo_calculation[$one_analyzed["id"]]["tv"] = isset($ainfo_calculation[$one_analyzed["id"]]["tv"])?($ainfo_calculation[$one_analyzed["id"]]["tv"]+$one_modem["add_info"][$one_analyzed["id"]]["value"]):$one_modem["add_info"][$one_analyzed["id"]]["value"];
                    }
                }
            }

            $std_tccr = Helper::stats_standard_deviation($ccr_std_array);
            $avarege_tccr = ($total_all_consumption_first!=0 ? (($total_all_consumption_last - $total_all_consumption_first)/$total_all_consumption_first):$total_all_consumption_last);

            $std_t3l_tcl = Helper::stats_standard_deviation($t3l_tcl_std_array);
            $avarage_t3l_tcl = $total_all_consumption_last !=0 ? ($total_t3_last/$total_all_consumption_last):0;
            $avarage_t3f_tcf = $total_all_consumption_last !=0 ? ($total_t3_first/$total_all_consumption_last):0;

            $one_break["consumption"] = array(
                "tcf"=>$total_all_consumption_first,
                "tcl"=>$total_all_consumption_last,
                "t1f"=>$total_t1_first,
                "t1l"=>$total_t1_last,
                "t2f"=>$total_t2_first,
                "t2l"=>$total_t2_last,
                "t3f"=>$total_t3_first,
                "t3l"=>$total_t3_last,
                "std_tccr" => $std_tccr,
                "avarege_tccr" => $avarege_tccr,
                "acr_pstd" => ($avarege_tccr + 2*$std_tccr),
                "acr_mstd" => ($avarege_tccr - 2*$std_tccr),
                "std_t3l_tcl" => $std_t3l_tcl,
                "avarage_t3l_tcl" => $avarage_t3l_tcl,
                "avarage_t3f_tcf" => $avarage_t3f_tcf,
                "t3l_tcl_pstd" => ($avarage_t3l_tcl + 2*$std_t3l_tcl),
                "t3l_tcl_mstd" => ($avarage_t3l_tcl - 2*$std_t3l_tcl),
                "min_change_ratio" => $min_change_ratio,
                "max_change_ratio" => $max_change_ratio,
                "min_t3l_tcl" => $min_t3l_tcl,
                "max_t3l_tcl" => $max_t3l_tcl

            );


            foreach ($ainfo_calculation as &$one_calculation){

                $one_calculation["std"] = Helper::stats_standard_deviation($one_calculation["std_array"]);
                $one_calculation["max_value"] = max($one_calculation["std_array"]);
                $one_calculation["min_value"] = min($one_calculation["std_array"]);
                unset($one_calculation["std_array"]);

                $one_calculation["avarage"] = $one_calculation["tcl"]/$one_calculation["tv"];
                $one_calculation["avarage_pstd"] = $one_calculation["avarage"] + 2*$one_calculation["std"];
                $one_calculation["avarage_mstd"] = $one_calculation["avarage"] - 2*$one_calculation["std"];

                unset($one_calculation["tcl"]);
                unset($one_calculation["tv"]);
            }

            $one_break["add_info"] = $ainfo_calculation;

        }

    }

    private function breakAdditionalInfo($additional_info){

        //handle additional_info
        $return_array = array();
        $add_info_ids = "";
        $add_info = json_decode($additional_info,true);
        if(COUNT($add_info)>0 && is_numeric($add_info[0]["id"])){

            foreach($add_info as $one_info){
                $add_info_ids .= $one_info["id"].",";
            }

            $add_info_ids = trim($add_info_ids,',');

            $a_info = DB::table("additional_infos")
                ->whereRaw("FIND_IN_SET(id,'".$add_info_ids."')>0 OR FIND_IN_SET(parent_id,'".$add_info_ids."')>0")
                ->get();

            foreach($add_info as $one_info){
                foreach($a_info as $one_a_info){
                    if($one_info["id"]==$one_a_info->id){
                        if($one_a_info->is_category == 1){
                            foreach($a_info as $c_a_info){
                                if($c_a_info->id."" == "".$one_info["value"]){

                                    $return_array[$one_info["id"]] = array("is_category"=>true,"value"=>$c_a_info->name);
                                    break;
                                }
                            }
                        }
                        else{

                            $return_array[$one_info["id"]] = array("is_category"=>false,"value"=>$one_info["value"]);
                        }

                        break;
                    }
                }
            }
        }

        return $return_array;
    }

    private function printBreakConsumptionData($objPHPExcel){
        //create for break based consumption sheet
        $objPHPExcel->createSheet(2);
        $objPHPExcel->setActiveSheetIndex(2);
        $break_consumption_sheet = $objPHPExcel->getActiveSheet();
        $break_consumption_sheet->setTitle(trans("reporting.break_based_consumption"));

        $this->prepareBreakConsumptionHeader($break_consumption_sheet);

        //create for break based modem consumption sheet
        $objPHPExcel->createSheet(3);
        $objPHPExcel->setActiveSheetIndex(3);
        $modem_consumption_sheet = $objPHPExcel->getActiveSheet();
        $modem_consumption_sheet->setTitle(trans("reporting.modems"));

        $this->prepareModemConsumptionHeader($modem_consumption_sheet);

        $just_modems_array = array();

        //handle break based consumption data
        foreach ($this->break_based_modems as $one_break){
            $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["break_name"].$this->break_cons_row_cursor,
                $one_break["description"]["break_name"]);

            $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["modem_count"].$this->break_cons_row_cursor,
                $one_break["description"]["modem_count"]);

            if(isset($one_break["consumption"])){
                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["tcf"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["tcf"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["tcl"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["tcl"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t1f"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["t1f"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t1l"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["t1l"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t2f"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["t2f"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t2l"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["t2l"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t3f"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["t3f"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t3l"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["t3l"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t3f_tcf"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["avarage_t3f_tcf"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t3l_tcl"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["avarage_t3l_tcl"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["tccr"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["avarege_tccr"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["max_tccr"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["max_change_ratio"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["min_tccr"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["min_change_ratio"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["std_tccr"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["std_tccr"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["tccr_pstd"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["acr_pstd"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["tccr_mstd"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["acr_mstd"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["avr_t3l_tcl"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["avarage_t3l_tcl"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["max_t3l_tcl"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["max_t3l_tcl"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["min_t3l_tcl"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["min_t3l_tcl"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["std_t3l_tcl"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["std_t3l_tcl"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t3l_tcl_pstd"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["t3l_tcl_pstd"],2));

                $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor["t3l_tcl_mstd"].$this->break_cons_row_cursor,
                    number_format($one_break["consumption"]["t3l_tcl_mstd"],2));
            }

            //handle addition info
            if(isset($one_break["add_info"])){
                foreach($one_break["add_info"] as $key=>$one_info){
                    $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor[$key].$this->break_cons_row_cursor, number_format($one_info["avarage"],2));

                    $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor[$key."_max"].$this->break_cons_row_cursor, number_format($one_info["max_value"],2));

                    $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor[$key."_min"].$this->break_cons_row_cursor, number_format($one_info["min_value"],2));

                    $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor[$key."_std"].$this->break_cons_row_cursor, number_format($one_info["std"],2));

                    $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor[$key."_pstd"].$this->break_cons_row_cursor, number_format($one_info["avarage_pstd"],2));

                    $break_consumption_sheet->SetCellValue($this->break_cons_column_cursor[$key."_mstd"].$this->break_cons_row_cursor, number_format($one_info["avarage_mstd"],2));

                }
            }

            if(isset($one_break["modems"])){
                foreach ($one_break["modems"] as $modem_key => &$just_modem){
                    $just_modem["break_info"] = array(
                        "std_tccr" => $one_break["consumption"]["std_tccr"],
                        "acr_pstd" => $one_break["consumption"]["acr_pstd"],
                        "acr_mstd" => $one_break["consumption"]["acr_mstd"],
                        "std_t3l_tcl" => $one_break["consumption"]["std_t3l_tcl"],
                        "t3l_tcl_pstd" => $one_break["consumption"]["t3l_tcl_pstd"],
                        "t3l_tcl_mstd" => $one_break["consumption"]["t3l_tcl_mstd"],
                        "add_info" => (isset($one_break["add_info"])?$one_break["add_info"]:array())
                    );

                    $just_modems_array[$modem_key] = $just_modem;
                }
            }

            $this->break_cons_row_cursor++;
        }

        //print just modems
        uasort($just_modems_array,function(&$a,&$b){
            $a_has_problem = false;
            $b_has_problem = false;

            $a_problem_reason = "";
            $b_problem_reason = "";

            //check for consumption
            if($a["consumption"]["tccr"] < $a["break_info"]["acr_mstd"] ||
                $a["consumption"]["tccr"]>$a["break_info"]["acr_pstd"]){

                $a_has_problem = true;
                $a_problem_reason = trans("reporting.consumption");
            }

            if($b["consumption"]["tccr"] < $b["break_info"]["acr_mstd"] ||
                $b["consumption"]["tccr"]>$b["break_info"]["acr_pstd"]){

                $b_has_problem = true;
                $b_problem_reason = trans("reporting.consumption");
            }

            //check for additional infos
            foreach($a["break_info"]["add_info"] as $key=>$one_info){
                if(isset($a["add_info"][$key]["ratio"])){
                    if($a["add_info"][$key]["ratio"] < $one_info["avarage_mstd"] || $a["add_info"][$key]["ratio"] > $one_info["avarage_pstd"]){
                        $a_has_problem = true;
                        $a_problem_reason = "ek bilgiler";
                    }
                }
            }

            foreach($b["break_info"]["add_info"] as $key=>$one_info){
                if(isset($b["add_info"][$key]["ratio"])){
                    if($b["add_info"][$key]["ratio"] < $one_info["avarage_mstd"] || $b["add_info"][$key]["ratio"] >
                        $one_info["avarage_pstd"]){
                        $b_has_problem = true;
                        $b_problem_reason = "ek bilgiler";
                    }
                }
            }

            if($a_has_problem == true){
                $this->modems_with_problems[$a["modem_id"]] = array("id"=>$a["modem_id"],"reason"=>$a_problem_reason);
                return -1;
            }
            else if($b_has_problem == true){

                $this->modems_with_problems[$b["modem_id"]] = array("id"=>$b["modem_id"],"reason"=>$b_problem_reason);
                return 1;
            }
            else {

                return 1;
            }
        });

        foreach ($just_modems_array as $one_modem){
            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["modem_no"].$this->modem_row_cursor, $one_modem["modem_no"]);

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["location"].$this->modem_row_cursor, $one_modem["location"]);
            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["client_name"].$this->modem_row_cursor, $one_modem["client_name"]);

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["tcf"].$this->modem_row_cursor, number_format($one_modem["consumption"]["tcf"],2));
            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["tcl"].$this->modem_row_cursor, number_format($one_modem["consumption"]["tcl"],2));
            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["tccr"].$this->modem_row_cursor, number_format($one_modem["consumption"]["tccr"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t1f"].$this->modem_row_cursor, number_format($one_modem["consumption"]["t1f"],2));
            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t1l"].$this->modem_row_cursor, number_format($one_modem["consumption"]["t1l"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t2f"].$this->modem_row_cursor, number_format($one_modem["consumption"]["t2f"],2));
            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t2l"].$this->modem_row_cursor, number_format($one_modem["consumption"]["t2l"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t3f"].$this->modem_row_cursor, number_format($one_modem["consumption"]["t3f"],2));
            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t3l"].$this->modem_row_cursor, number_format($one_modem["consumption"]["t3l"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t3f_tcf"].$this->modem_row_cursor, number_format($one_modem["consumption"]["t3f_tcf"],2));
            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t3l_tcl"].$this->modem_row_cursor, number_format($one_modem["consumption"]["t3l_tcl"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["std_tccr"].$this->modem_row_cursor,
                number_format($one_modem["break_info"]["std_tccr"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["tccr_pstd"].$this->modem_row_cursor, number_format($one_modem["break_info"]["acr_pstd"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["tccr_mstd"].$this->modem_row_cursor, number_format($one_modem["break_info"]["acr_mstd"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["std_t3l_tcl"].$this->modem_row_cursor, number_format($one_modem["break_info"]["std_t3l_tcl"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t3l_tcl_pstd"].$this->modem_row_cursor, number_format($one_modem["break_info"]["t3l_tcl_pstd"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["t3l_tcl_mstd"].$this->modem_row_cursor, number_format($one_modem["break_info"]["t3l_tcl_mstd"],2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["taf"].$this->modem_row_cursor, number_format($one_modem["consumption"]["taf"], 2));

            $modem_consumption_sheet->SetCellValue($this->modem_column_cursor["tal"].$this->modem_row_cursor, number_format($one_modem["consumption"]["tal"], 2));

            $last_column = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString($this->modem_column_cursor["tal"])+1);

            foreach($one_modem["add_info"] as $key=>$one_modem_info){
                if($one_modem_info["is_category"] == false){
                    if(isset($one_modem_info["ratio"])){
                        $modem_consumption_sheet->SetCellValue($this->modem_column_cursor[$key]
                            .$this->modem_row_cursor, number_format($one_modem_info["ratio"],2));
                    }
                }
            }

            foreach($one_modem["break_info"]["add_info"] as $key=>$one_info){
                $modem_consumption_sheet->SetCellValue($this->modem_column_cursor[$key."_std"].$this->modem_row_cursor, number_format($one_info["std"],2));

                $modem_consumption_sheet->SetCellValue($this->modem_column_cursor[$key."_pstd"].$this->modem_row_cursor, number_format($one_info["avarage_pstd"],2));

                $modem_consumption_sheet->SetCellValue($this->modem_column_cursor[$key."_mstd"].$this->modem_row_cursor, number_format($one_info["avarage_mstd"],2));

                $last_column = $this->modem_column_cursor[$key."_mstd"];

            }

            //check if this out of boundaries
            if(isset($this->modems_with_problems[$one_modem["modem_id"]]) == true)

                $modem_consumption_sheet->getStyle('A'.$this->modem_row_cursor.':'.$last_column.$this->modem_row_cursor)
                    ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'E05CC2')
                            )
                        )
                    );
            $this->modem_row_cursor++;
        }

    }

    private function prepareBreakConsumptionHeader($sheet){

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'font' => [
                'size' => 12,
                'bold' => true
            ],
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );


        $sheet->SetCellValue('A1',trans("reporting.break_name"));
        $this->break_cons_column_cursor["break_name"] = "A";

        $sheet->SetCellValue('B1',trans("reporting.modem_count"));
        $this->break_cons_column_cursor["modem_count"] = "B";

        $sheet->SetCellValue('C1',trans("reporting.tcf"));
        $this->break_cons_column_cursor["tcf"] = "C";

        $sheet->SetCellValue('D1',trans("reporting.tcl"));
        $this->break_cons_column_cursor["tcl"] = "D";

        $sheet->SetCellValue('E1',trans("reporting.t1f"));
        $this->break_cons_column_cursor["t1f"] = "E";

        $sheet->SetCellValue('F1',trans("reporting.t1l"));
        $this->break_cons_column_cursor["t1l"] = "F";

        $sheet->SetCellValue('G1',trans("reporting.t2f"));
        $this->break_cons_column_cursor["t2f"] = "G";

        $sheet->SetCellValue('H1',trans("reporting.t2l"));
        $this->break_cons_column_cursor["t2l"] = "H";

        $sheet->SetCellValue('I1',trans("reporting.t3f"));
        $this->break_cons_column_cursor["t3f"] = "I";

        $sheet->SetCellValue('J1',trans("reporting.t3l"));
        $this->break_cons_column_cursor["t3l"] = "J";

        $sheet->SetCellValue('K1',trans("reporting.average")."(".trans("reporting.t3f")."/".trans("reporting.tcf").")");
        $this->break_cons_column_cursor["t3f_tcf"] = "K";

        $sheet->SetCellValue('L1',trans("reporting.average")."(".trans("reporting.t3l")."/".trans("reporting.tcl").")");
        $this->break_cons_column_cursor["t3l_tcl"] = "L";

        $sheet->SetCellValue('M1',trans("reporting.tccr"));
        $this->break_cons_column_cursor["tccr"] = "M";

        $sheet->SetCellValue('N1',"MAX(".trans("reporting.tccr").")");
        $this->break_cons_column_cursor["max_tccr"] = "N";

        $sheet->SetCellValue('O1',"MIN(".trans("reporting.tccr").")");
        $this->break_cons_column_cursor["min_tccr"] = "O";

        $sheet->SetCellValue('P1',trans("reporting.std")." (".trans("reporting.tccr").")");
        $this->break_cons_column_cursor["std_tccr"] = "P";

        $sheet->SetCellValue('Q1',trans('reporting.average') . "(" . trans("reporting.cr").") + 2std");
        $this->break_cons_column_cursor["tccr_pstd"] = "Q";

        $sheet->SetCellValue('R1',trans('reporting.average') . "(" . trans("reporting.cr").") - 2std");
        $this->break_cons_column_cursor["tccr_mstd"] = "R";

        $sheet->SetCellValue('S1',trans('reporting.average') . "(" . trans("reporting.t3l")."/".trans("reporting.tcl").")");
        $this->break_cons_column_cursor["avr_t3l_tcl"] = "S";

        $sheet->SetCellValue('T1',"MAX (" . trans("reporting.t3l")."/".trans("reporting.tcl").")");
        $this->break_cons_column_cursor["max_t3l_tcl"] = "T";

        $sheet->SetCellValue('U1',"MIN (" . trans("reporting.t3l")."/".trans("reporting.tcl").")");
        $this->break_cons_column_cursor["min_t3l_tcl"] = "U";

        $sheet->SetCellValue('V1',trans("reporting.std")."(" . trans("reporting.t3l")."/".trans("reporting.tcl")
        .")");
        $this->break_cons_column_cursor["std_t3l_tcl"] = "V";

        $sheet->SetCellValue('W1',trans('reporting.average')."(" . trans("reporting.t3l")."/".trans("reporting.tcl").") + 2std");
        $this->break_cons_column_cursor["t3l_tcl_pstd"] = "W";

        $sheet->SetCellValue('X1',trans('reporting.average')."(" . trans("reporting.t3l")."/".trans("reporting.tcl").") - 2std");
        $this->break_cons_column_cursor["t3l_tcl_mstd"] = "X";

        $column = 23;

        foreach($this->ainfo_parsed["analyzed"] as $one_info){

            $sheet->setCellValueByColumnAndRow(++$column, 1, trans("reporting.average")."(".trans("reporting.tcl")."/"
                .$one_info["name"]
                .")");

            $this->break_cons_column_cursor[$one_info["id"]] = PHPExcel_Cell::stringFromColumnIndex($column);

            $sheet->setCellValueByColumnAndRow(++$column, 1, "MAX(".trans("reporting.tcl")."/"
                .$one_info["name"]
                .")");
            $this->break_cons_column_cursor[$one_info["id"]."_max"] = PHPExcel_Cell::stringFromColumnIndex($column);

            $sheet->setCellValueByColumnAndRow(++$column, 1, "MIN(".trans("reporting.tcl")."/"
                .$one_info["name"]
                .")");
            $this->break_cons_column_cursor[$one_info["id"]."_min"] = PHPExcel_Cell::stringFromColumnIndex($column);

            $sheet->setCellValueByColumnAndRow(++$column, 1, trans("reporting.std")."(".trans("reporting.tcl")."/"
                .$one_info["name"]
                .")");
            $this->break_cons_column_cursor[$one_info["id"]."_std"] = PHPExcel_Cell::stringFromColumnIndex($column);

            $sheet->setCellValueByColumnAndRow(++$column, 1, trans('reporting.average')."(".trans("reporting.tcl")."/"
                .$one_info["name"]
                .") + 2std");
            $this->break_cons_column_cursor[$one_info["id"]."_pstd"] = PHPExcel_Cell::stringFromColumnIndex($column);

            $sheet->setCellValueByColumnAndRow(++$column, 1, trans('reporting.average')."(".trans("reporting.tcl")."/"
                .$one_info["name"]
                .") - 2std");
            $this->break_cons_column_cursor[$one_info["id"]."_mstd"] = PHPExcel_Cell::stringFromColumnIndex($column);

        }

        $sheet->getStyle("A1:".PHPExcel_Cell::stringFromColumnIndex($column)."1")->applyFromArray($style);

        $sheet->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D:'.PHPExcel_Cell::stringFromColumnIndex($column))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        for($i=0; $i<=$column; $i++){
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    private function prepareBreakGroupByData($base_type){

        //setup first initializations

        if($base_type == "break"){
            foreach ($this->break_based_modems as $one_break){

                if(isset($one_break["modems"])){

                    foreach ($one_break["modems"] as $key=>$one_modem){

                        foreach ($this->ainfo_parsed["grouped_by_values"] as $one_ainfo){

                            if(isset($one_modem["add_info"][$one_ainfo["id"]]["value"])){

                                $this->break_based_group_by[$one_ainfo["id"]]["elements"][$one_modem["add_info"][$one_ainfo["id"]]["value"]]["modems"][$key] =  $one_modem;

                                $this->break_based_group_by[$one_ainfo["id"]]["name"] = $one_ainfo["name"];
                            }
                        }

                    }
                }
            }
        }
        else if($base_type == "modem"){

            //print_r($this->modem_based_data);
            if(isset($this->modem_based_data["modems"])){
                foreach ($this->modem_based_data["modems"] as $key=>$one_modem){

                    foreach ($this->ainfo_parsed["grouped_by_values"] as $one_ainfo){


                        if(isset($one_modem["modem"]["add_info"][$one_ainfo["id"]]["value"])){

                            $this->break_based_group_by[$one_ainfo["id"]]["elements"][$one_modem["modem"]["add_info"][$one_ainfo["id"]]["value"]]["modems"][$key] =  $one_modem;

                            $this->break_based_group_by[$one_ainfo["id"]]["name"] = $one_ainfo["name"];
                        }
                    }
                }
            }


        }



        //prepare data
        foreach ($this->break_based_group_by as &$one_group_by){

            if(!isset($one_group_by["elements"]))
                continue;

            foreach ($one_group_by["elements"] as $key => &$one_item){

                $ccr_std_array = array();
                $total_all_consumption_first = 0;
                $total_all_consumption_last = 0;

                $total_t1_first = 0;
                $total_t1_last = 0;

                $total_t2_first = 0;
                $total_t2_last = 0;

                $total_t3_first = 0;
                $total_t3_last = 0;

                $t3l_tcl_std_array = array();

                $ainfo_calculation = array();

                $min_change_ratio = false;
                $max_change_ratio = false;

                $min_t3l_tcl = false;
                $max_t3l_tcl = false;

                $modem_count = 0;

                if(!isset($one_item["modems"]))
                    continue;

                foreach($one_item["modems"] as $modem_key=>&$one_modem){

                    if($base_type == "break")
                        $the_modem_c  = $one_modem["consumption"];
                    else
                        $the_modem_c = $one_modem;

                    $ccr_std_array[] = $the_modem_c["tccr"];
                    $total_all_consumption_first +=  $the_modem_c["tcf"];
                    $total_all_consumption_last +=  $the_modem_c["tcl"];

                    $total_t1_first += $the_modem_c["t1f"];
                    $total_t1_last += $the_modem_c["t1l"];

                    $total_t2_first += $the_modem_c["t2f"];
                    $total_t2_last += $the_modem_c["t2l"];

                    $total_t3_first += $the_modem_c["t3f"];
                    $total_t3_last += $the_modem_c["t3l"];

                    $t3l_tcl_std_array[] = $the_modem_c["t3l_tcl"];


                    if($min_change_ratio == false)
                        $min_change_ratio = $the_modem_c["tccr"];
                    else{
                        if($the_modem_c["tccr"] < $min_change_ratio)
                            $min_change_ratio = $the_modem_c["tccr"];
                    }

                    if($max_change_ratio == false)
                        $max_change_ratio = $the_modem_c["tccr"];
                    else{
                        if($the_modem_c["tccr"] > $max_change_ratio)
                            $max_change_ratio = $the_modem_c["tccr"];
                    }


                    if($min_t3l_tcl == false)
                        $min_t3l_tcl = $the_modem_c["t3l_tcl"];
                    else{
                        if($the_modem_c["t3l_tcl"] < $min_t3l_tcl)
                            $min_t3l_tcl = $the_modem_c["t3l_tcl"];
                    }

                    if($max_t3l_tcl == false)
                        $max_t3l_tcl = $the_modem_c["t3l_tcl"];
                    else{
                        if($the_modem_c["t3l_tcl"] > $max_t3l_tcl)
                            $max_t3l_tcl = $the_modem_c["t3l_tcl"];
                    }


                    foreach ($this->ainfo_parsed["analyzed"] as $one_analyzed){

                        if($base_type == "break")
                            $the_add_info = $one_modem["add_info"];
                        else
                            $the_add_info = $one_modem["modem"]["add_info"];

                        if(isset($the_add_info[$one_analyzed["id"]]) && is_numeric
                            ($the_add_info[$one_analyzed["id"]]["value"]) &&
                            $the_add_info[$one_analyzed["id"]]["value"]
                            != 0){

                            $the_add_info[$one_analyzed["id"]]["ratio"] = $the_modem_c["tcl"] / $the_add_info[$one_analyzed["id"]]["value"];

                            $ainfo_calculation[$one_analyzed["id"]]["std_array"][] = $the_add_info[$one_analyzed["id"]]["ratio"];

                            $ainfo_calculation[$one_analyzed["id"]]["tcl"] = isset($ainfo_calculation[$one_analyzed["id"]]["tcl"])?($ainfo_calculation[$one_analyzed["id"]]["tcl"] + $the_modem_c["tcl"]):$the_modem_c["tcl"];

                            $ainfo_calculation[$one_analyzed["id"]]["tv"] = isset($ainfo_calculation[$one_analyzed["id"]]["tv"])?($ainfo_calculation[$one_analyzed["id"]]["tv"]+$the_add_info[$one_analyzed["id"]]["value"]):$the_add_info[$one_analyzed["id"]]["value"];
                        }
                    }

                    $modem_count++;
                }

                $std_tccr = Helper::stats_standard_deviation($ccr_std_array);
                $avarege_tccr = ($total_all_consumption_first!=0 ? (($total_all_consumption_last - $total_all_consumption_first)/$total_all_consumption_first):$total_all_consumption_last);

                $std_t3l_tcl = Helper::stats_standard_deviation($t3l_tcl_std_array);
                $avarage_t3l_tcl = $total_all_consumption_last !=0 ? ($total_t3_last/$total_all_consumption_last):0;
                $avarage_t3f_tcf = $total_all_consumption_last !=0 ? ($total_t3_first/$total_all_consumption_last):0;

                $one_item["consumption"] = array(
                    "tcf"=>$total_all_consumption_first,
                    "tcl"=>$total_all_consumption_last,
                    "t1f"=>$total_t1_first,
                    "t1l"=>$total_t1_last,
                    "t2f"=>$total_t2_first,
                    "t2l"=>$total_t2_last,
                    "t3f"=>$total_t3_first,
                    "t3l"=>$total_t3_last,
                    "std_tccr" => $std_tccr,
                    "avarege_tccr" => $avarege_tccr,
                    "acr_pstd" => ($avarege_tccr + 2*$std_tccr),
                    "acr_mstd" => ($avarege_tccr - 2*$std_tccr),
                    "std_t3l_tcl" => $std_t3l_tcl,
                    "avarage_t3l_tcl" => $avarage_t3l_tcl,
                    "avarage_t3f_tcf" => $avarage_t3f_tcf,
                    "t3l_tcl_pstd" => ($avarage_t3l_tcl + 2*$std_t3l_tcl),
                    "t3l_tcl_mstd" => ($avarage_t3l_tcl - 2*$std_t3l_tcl),
                    "min_change_ratio" => $min_change_ratio,
                    "max_change_ratio" => $max_change_ratio,
                    "min_t3l_tcl" => $min_t3l_tcl,
                    "max_t3l_tcl" => $max_t3l_tcl,
                    "modem_count" => $modem_count
                );


                foreach ($ainfo_calculation as &$one_calculation){

                    $one_calculation["std"] = Helper::stats_standard_deviation($one_calculation["std_array"]);
                    $one_calculation["max_value"] = max($one_calculation["std_array"]);
                    $one_calculation["min_value"] = min($one_calculation["std_array"]);
                    unset($one_calculation["std_array"]);

                    $one_calculation["avarage"] = $one_calculation["tcl"]/$one_calculation["tv"];
                    $one_calculation["avarage_pstd"] = $one_calculation["avarage"] + 2*$one_calculation["std"];
                    $one_calculation["avarage_mstd"] = $one_calculation["avarage"] - 2*$one_calculation["std"];

                    unset($one_calculation["tcl"]);
                    unset($one_calculation["tv"]);
                }

                $one_item["add_info"] = $ainfo_calculation;

                unset($one_item["modems"]);
            }
        }

        //print_r($this->break_based_group_by);
        //exit;
    }

    private function printBreakGroupBy($objPHPExcel, $sheet_counter){

        foreach ($this->break_based_group_by as $one_group_by){

            $objPHPExcel->createSheet($sheet_counter);
            $objPHPExcel->setActiveSheetIndex($sheet_counter);
            $group_sheet = $objPHPExcel->getActiveSheet();
            $invalidCharacters = $group_sheet->getInvalidCharacters();

            $group_sheet->setTitle(str_replace($invalidCharacters, '', $one_group_by["name"]));
            $this->prepareBreakConsumptionHeader($group_sheet);

            $row_cursor = 2;

            if(!isset($one_group_by["elements"]))
                continue;
            foreach ($one_group_by["elements"] as $key=>$one_item){

                $group_sheet->SetCellValue($this->break_cons_column_cursor["break_name"].$row_cursor,
                    $key);

                if(isset($one_item["consumption"])){

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["modem_count"].$row_cursor,
                        $one_item["consumption"]["modem_count"]);

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["tcf"].$row_cursor,
                        number_format($one_item["consumption"]["tcf"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["tcl"].$row_cursor,
                        number_format($one_item["consumption"]["tcl"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t1f"].$row_cursor,
                        number_format($one_item["consumption"]["t1f"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t1l"].$row_cursor,
                        number_format($one_item["consumption"]["t1l"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t2f"].$row_cursor,
                        number_format($one_item["consumption"]["t2f"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t2l"].$row_cursor,
                        number_format($one_item["consumption"]["t2l"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t3f"].$row_cursor,
                        number_format($one_item["consumption"]["t3f"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t3l"].$row_cursor,
                        number_format($one_item["consumption"]["t3l"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t3f_tcf"].$row_cursor,
                        number_format($one_item["consumption"]["avarage_t3f_tcf"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t3l_tcl"].$row_cursor,
                        number_format($one_item["consumption"]["avarage_t3l_tcl"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["tccr"].$row_cursor,
                        number_format($one_item["consumption"]["avarege_tccr"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["max_tccr"].$row_cursor,
                        number_format($one_item["consumption"]["max_change_ratio"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["min_tccr"].$row_cursor,
                        number_format($one_item["consumption"]["min_change_ratio"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["std_tccr"].$row_cursor,
                        number_format($one_item["consumption"]["std_tccr"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["tccr_pstd"].$row_cursor,
                        number_format($one_item["consumption"]["acr_pstd"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["tccr_mstd"].$row_cursor,
                        number_format($one_item["consumption"]["acr_mstd"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["avr_t3l_tcl"].$row_cursor,
                        number_format($one_item["consumption"]["avarage_t3l_tcl"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["max_t3l_tcl"].$row_cursor,
                        number_format($one_item["consumption"]["max_t3l_tcl"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["min_t3l_tcl"].$row_cursor,
                        number_format($one_item["consumption"]["min_t3l_tcl"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["std_t3l_tcl"].$row_cursor,
                        number_format($one_item["consumption"]["std_t3l_tcl"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t3l_tcl_pstd"].$row_cursor,
                        number_format($one_item["consumption"]["t3l_tcl_pstd"],2));

                    $group_sheet->SetCellValue($this->break_cons_column_cursor["t3l_tcl_mstd"].$row_cursor,
                        number_format($one_item["consumption"]["t3l_tcl_mstd"],2));
                }



                //handle addition info
                if(isset($one_item["add_info"])){
                    foreach($one_item["add_info"] as $key=>$one_info){

                        $group_sheet->SetCellValue($this->break_cons_column_cursor[$key].$row_cursor, number_format
                        ($one_info["avarage"],2));

                        $group_sheet->SetCellValue($this->break_cons_column_cursor[$key."_max"].$row_cursor, number_format($one_info["max_value"],2));

                        $group_sheet->SetCellValue($this->break_cons_column_cursor[$key."_min"].$row_cursor, number_format($one_info["min_value"],2));

                        $group_sheet->SetCellValue($this->break_cons_column_cursor[$key."_std"].$row_cursor, number_format($one_info["std"],2));

                        $group_sheet->SetCellValue($this->break_cons_column_cursor[$key."_pstd"].$row_cursor, number_format($one_info["avarage_pstd"],2));

                        $group_sheet->SetCellValue($this->break_cons_column_cursor[$key."_mstd"].$row_cursor, number_format($one_info["avarage_mstd"],2));

                    }
                }


                $row_cursor++;

            }
        }

    }

    private function AInfoParser(){

        $filtered_verbal = "";
        $filtered_values = array();
        $analyzed_values = array();
        $grouped_by_values = array();

        foreach ($this->ainfo_detail as $one_detail){
            foreach ($this->dist_ainfo as $one_info){
                if($one_detail["type"] == "filter"){

                    if($one_info->id == $one_detail["value"]){


                        $filtered_verbal .=$one_info->parent_name.": ".$one_info->name."\n";
                        $filtered_values[] = array("id"=>$one_info->parent_id,"value"=>$one_info->id);
                    }
                }
                else if($one_detail["type"] == "ainfo"){
                    if($one_info->id == $one_detail["id"]){
                        $analyzed_values[] = array("id"=>$one_info->id,"name"=>$one_info->name);
                    }
                }
                else if($one_detail["type"] == "category"){

                    if($one_info->id == $one_detail["id"]){
                        $grouped_by_values[$one_info->id] = array("id"=>$one_info->id,"name"=>$one_info->name);
                    }

                }
            }
        }

        if( $filtered_verbal == "" ){
            $this->ainfo_parsed["filtered_verbal"] = trans("reporting.none");
        }
        else{
            $this->ainfo_parsed["filtered_verbal"] = trim($filtered_verbal);
        }

        $this->ainfo_parsed["filtered_value"] = $filtered_values;
        $this->ainfo_parsed["analyzed"] = $analyzed_values;
        $this->ainfo_parsed["grouped_by_values"] = $grouped_by_values;

    }


//####### WORD FUNCTIONS #######
    private  function createWord($report){

    }


//####### COMMON FUNCTIONS #######
    private function update($report, $document_name){
        $updated_colums = array(
            'document_name' => $document_name,
            'status' => 1
        );

        $updated_colums['detail->comparison_start'] = $this->data_start_date_v;
        $updated_colums['detail->comparison_end'] = $this->data_end_date_v;

        DB::table('reports')
            ->where('id', $report->id)
            ->where('is_report',1)
            ->update($updated_colums);

        // update first_run_time and last_run_time to report's template
        if($report->template_id != "" && is_numeric($report->template_id)){
            DB::update(
                'UPDATE reports 
                SET 
                  last_run_time = ?, 
                  first_run_time = COALESCE(first_run_time, ?) 
                WHERE 
                  is_report = 0 
                  AND 
                  id = ?',
                array(
                    date("Y-m-d H:i:s"),
                    date("Y-m-d H:i:s"),
                    $report->template_id
                )
            );
        }
    }

    private function getOrgPath($all_structure,$id){
        if($id==0)
            return "";
        foreach($all_structure as $one_node){
            if($one_node->id == $id){
                if($one_node->parent_id != 0)
                    return $one_node->name."/".$this->getOrgPath($all_structure,$one_node->parent_id);
                else
                    return $this->getOrgPath($all_structure,$one_node->parent_id);
            }
        }
    }

    private function getParentList($all_structure, $org_id){

        $parent_list = array();
        $parent_list[] = $org_id;

        foreach ($all_structure as $one_node){

            foreach ($parent_list as $one_list){
                if($one_list == $one_node->parent_id){
                    $parent_list[] = $one_node->id;
                    break;
                }
            }
        }

        return $parent_list;
    }

    private function sendMail(){

        if(COUNT($this->email_list > 0)){
            $detail_info = "
                    <table cellpadding='1'>
                        <tbody>
                            <tr height='10px'></tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ". $this->mail_detail_info["company"]["title"]." </td>
                                <td> : <b>".$this->mail_detail_info["company"]["value"]." </b></td>
                            </tr> 
                            <tr>
                                <td width='10px'></td>
                                <td> ".$this->mail_detail_info["created_by"]["title"]." </td>
                                <td> : <b>".$this->mail_detail_info["created_by"]["value"]." </b></td>
                            </tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ".$this->mail_detail_info["created_at"]["title"]." </td>
                                <td> : <b>".$this->mail_detail_info["created_at"]["value"]." </b></td>
                            </tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ".$this->mail_detail_info["report_type"]["title"]." </td>
                                <td> : <b>".$this->mail_detail_info["report_type"]["value"]." </b></td>
                            </tr>
                            <tr height='10px'></tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ".$this->mail_detail_info["working_type"]["title"]." </td>
                                <td> : <b> ".$this->mail_detail_info["working_type"]["value"]." </b></td>
                            </tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ".$this->mail_detail_info["comparison_type"]["title"]." </td>
                                <td> : <b>".$this->mail_detail_info["comparison_type"]["value"]." </b></td>
                            </tr> 
                            <tr>
                                <td width='10px'></td>
                                <td> ".$this->mail_detail_info["first_date"]["title"]." </td>
                                <td> : <b>".$this->mail_detail_info["first_date"]["value"]." </b></td>
                            </tr> 
                            <tr>
                                <td width='10px'></td>
                                <td> ".$this->mail_detail_info["last_date"]["title"]." </td>
                                <td> : <b>".$this->mail_detail_info["last_date"]["value"]." </b></td>
                            </tr> 
                            <tr>
                                <td width='10px'></td>
                                <td> ".$this->mail_detail_info["applied_filters"]["title"]." </td>
                                <td> : <b>".$this->mail_detail_info["applied_filters"]["value"]." </b></td>
                            </tr> 
                            
                            <tr height='10px'></tr>
                        </tbody>
                    </table>";
            $data = array(
                "type" => "report",
                "subject" => trans("reporting.mail_subject"),
                "title" => $this->mail_detail_info["report_name"],
                "detail_exp" => trans('reporting.mail_report_detail_exp'),
                "detail_info" => $detail_info
            );

            Mail::send("mail.alert", $data, function($message) use ($data) {

                $message->to($this->email_list)->subject($data["subject"]);
                $message->from(env('MAIL_USERNAME'), trans("global.mail_sender_name"));
                $message->attach($this->mail_detail_info["attachment_path"]);
            });

            if( count(Mail::failures())>0 ){
                return  "ERROR";
            }
            else{
                return "SUCCESS";
            }
        }

    }

}
