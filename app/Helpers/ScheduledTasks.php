<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ScheduledTasks
{

    /*
     * This function is called periodically which is determined in /app/console/kernel.php, to summarize the data based on their daily calculations
     *
     */
    public static function summarizeByDay(){


    }

    public static function detectAlarms(){
        //get active devices which have alert definitions
        $all_devices = DB::table('devices as D')
                ->select('D.*', 'M.last_connection_at as modem_last_connection', 'DT.type as device_type', 'M.serial_no as modem_serial_no', 'M.id as modem_id', 'C.name as client_name', 'DD.name as distributor_name')
                ->join('device_type as DT','DT.id','D.device_type_id')
                ->join('modems as M','M.id','D.modem_id')
                ->join('clients as C','C.id','M.client_id')
                ->leftJoin('distributors as DD','DD.id','C.distributor_id')
                ->where('D.status','<>',0)
                ->where('D.alert_definitions','<>','')
                ->where('M.status','<>',0)
                ->get();
				
				
		print_r($all_devices);		
				

        //get all alert definitions to use rest of the code
        $all_definitions = array();
        $tmp_definitions = DB::table('alert_definitions as AD')
            ->where('status','<>',0)
            ->get();


        foreach ($tmp_definitions as $one_definition){
            $all_definitions[$one_definition->id] = $one_definition;
        }
		
		
        $checked_modems = array();

		
        foreach($all_devices as $one_device){
            // Get the alarm definitions for each device
			
            $device_definitions_array = $one_device->alert_definitions;
            $device_definitions = array_filter($all_definitions, function($key) USE ($device_definitions_array){
                $device_definitions_array = explode(',', $device_definitions_array);
                return in_array($key, $device_definitions_array);
            }, ARRAY_FILTER_USE_KEY);

            foreach ($device_definitions as $one_definition){
                if($one_definition->type == "reactive"){
                    self::reactiveController(
                        $one_definition->id,
                        $one_definition->name,
                        $one_definition->policy,
                        $one_device->id,
                        $one_device->device_type,
                        $one_device->invoice_day,
                        $one_device->modem_serial_no,
                        $one_device->device_no,
                        $one_device->multiplier,
                        $one_device->alert_emails,
                        $one_device->alert_phones,
                        $one_device->client_name,
                        $one_device->distributor_name
                    );
                }
                else if($one_definition->type == "current"){
                    self::currentController(
                        $one_definition->id,
                        $one_definition->name,
                        $one_definition->policy,
                        $one_device->id,
                        $one_device->device_type,
                        $one_device->modem_serial_no,
                        $one_device->device_no,
                        $one_device->alert_emails,
                        $one_device->alert_phones,
                        $one_device->client_name,
                        $one_device->distributor_name
                    );
                }
                else if($one_definition->type == "voltage"){
                    self::voltageController(
                        $one_definition->id,
                        $one_definition->name,
                        $one_definition->policy,
                        $one_device->id,
                        $one_device->device_type,
                        $one_device->modem_serial_no,
                        $one_device->device_no,
                        $one_device->alert_emails,
                        $one_device->alert_phones,
                        $one_device->client_name,
                        $one_device->distributor_name
                    );
                }
                else if($one_definition->type == "connection"){
                    // check the connection of the Modem of the device, if it has not been checked before
                    // Thus all modems used in the system is checked.
                    if( !in_array($one_device->modem_id, $checked_modems) ){
                        $checked_modems[] = $one_device->modem_id;

                        self::connectionController(
                            $one_definition->id,
                            $one_definition->name,
                            $one_definition->policy,
                            $one_device->id,
                            $one_device->modem_id,
                            "modem",
                            $one_device->modem_serial_no,
                            $one_device->modem_last_connection,
                            $one_device->alert_emails,
                            $one_device->alert_phones,
                            $one_device->client_name,
                            $one_device->distributor_name
                        );
                    }

                    // check device connection status
                    self::connectionController(
                        $one_definition->id,
                        $one_definition->name,
                        $one_definition->policy,
                        $one_device->id,
                        $one_device->modem_id,
                        $one_device->device_type,
                        $one_device->device_no,
                        $one_device->last_data_at,
                        $one_device->alert_emails,
                        $one_device->alert_phones,
                        $one_device->client_name,
                        $one_device->distributor_name
                    );
                }
            }
        }
    }

    private static function reactiveController($definition_id, $definition_name, $policy, $device_id, $device_type, $invoice_day, $modem_serial_no, $device_serial_no, $multiplier, $emails, $phones, $client, $distributor)
    {
        $policy = json_decode($policy);

        $table_name = "device_records_meter";
        if($device_type == "analyzer" || $device_type == "relay")
            $table_name = "device_records_modbus";

        $detail = array(
            "inductive" => array(
                "definition_id" => $definition_id,
                "action" => array()
            ),
            "capacitive" => array(
                "definition_id" => $definition_id,
                "action" => array()
            )
        );

        if( isset($policy->notification) ){
            if(self::isTimeToRun($device_id, "reactive", "notification", $policy->notification->notification_period)){

                self::reactiveAlertDetector($policy, "notification", $detail, $table_name, $modem_serial_no,
                    $device_serial_no,$invoice_day,$multiplier,$definition_id);

            } // end of the isTimeToRun control
        } // end of the policy->notification

        if( isset($policy->email) ){
            if(self::isTimeToRun($device_id,"reactive","email",$policy->email->notification_period)){
                self::reactiveAlertDetector($policy, "email", $detail, $table_name, $modem_serial_no,
                    $device_serial_no,$invoice_day,$multiplier,$definition_id);
            }
        }

        if(isset($policy->sms)){
            if(self::isTimeToRun($device_id,"reactive","sms",$policy->sms->notification_period)){
                self::reactiveAlertDetector($policy, "sms", $detail, $table_name, $modem_serial_no,
                    $device_serial_no,$invoice_day,$multiplier,$definition_id);
            }
        }

        //perform the actions
        if( COUNT($detail["inductive"]["action"]) > 0 ){
            $ratio = 0;

            if( in_array("email", $detail["inductive"]["action"]) ){
                $ratio = $detail["inductive"]["email"]["ratio"];

                $data_interval = date('d/m/Y H:i', strtotime($detail["inductive"]["email"]["start_date"])) . "<br /> &nbsp;&nbsp;" . date('d/m/Y H:i', strtotime($detail["inductive"]["email"]["end_date"]));
                $extra_row = "
                                <tr>
                                    <td width='10px'></td>
                                    <td> ".trans('alerts.inductive_limit')." </td>
                                    <td> : <b> % ". $policy->email->inductive_limit ." </b></td>
                                </tr>";
                $related_data = "
                                <tr>
                                    <td width='10px'></td>
                                    <td> ".trans('alerts.inductive_ratio')." </td>
                                    <td style='color: #cc0000;'> : <b> % ". number_format($detail["inductive"]["email"]["ratio"],
                        2) ." </b></td>
                                </tr>";

                $detail_info = self::prepareMailDetail($definition_name, $device_type, $device_serial_no, $client, $distributor, $data_interval, $related_data, $extra_row);

                $data = array(
                    "type" => "reactive",
                    "subject" => trans("alerts.mail_reactive_subject"),
                    "title" => trans("alerts.detail_reactive_exp", array("sub_type"=>trans("alerts.inductive"))),
                    "detail_exp" => trans('alerts.mail_reactive_detail_exp'),
                    "detail_info" => $detail_info,
                    "to" => $emails
                );

                self::sendEmail($data);
            }

            if( in_array("sms", $detail["inductive"]["action"]) ){
                if( $detail["inductive"]["sms"]["ratio"] > $ratio ){
                    $ratio = $detail["inductive"]["sms"]["ratio"];
                }

                $body  = trans('alerts.mail_reactive_subject') . "\r\n";
                $body .= $client . "\r\n";
                $body .= trans('alerts.device') . ": " . $device_serial_no . " (".trans('global.'.$device_type).")" . "\r\n";
                $body .= trans('alerts.inductive') . ": %" . number_format($detail["inductive"]["email"]["ratio"],1);

                self::sendSms($body, $phones);
            }

            if( $detail["inductive"]["notification"]["ratio"] > $ratio ){
                $ratio = $detail["inductive"]["notification"]["ratio"];
            }

            $detail["ratio"] = $ratio;

            // An alert is always created
            self::createNotification("reactive", "inductive", $device_id, json_encode($detail["inductive"]));
        }

        if( COUNT($detail["capacitive"]["action"])>0 ){
            $ratio = 0;

            if(in_array("email", $detail["capacitive"]["action"])){
                $ratio = $detail["capacitive"]["email"]["ratio"];

                $data_interval = date('d/m/Y H:i', strtotime($detail["capacitive"]["email"]["start_date"])) . "<br /> &nbsp;&nbsp;" . date('d/m/Y H:i', strtotime($detail["capacitive"]["email"]["end_date"]));
                $extra_row = "<tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.capacitive_limit')." </td>
                                <td> : <b> % ". $policy->email->capacitive_limit ." </b></td>
                            </tr>";
                $related_data = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.capacitive_ratio')." </td>
                                <td style='color: #cc0000;'> : <b> % ". number_format($detail["capacitive"]["email"]["ratio"], 2)
                    ." </b></td>
                            </tr>";

                $detail_info = self::prepareMailDetail($definition_name, $device_type, $device_serial_no, $client, $distributor, $data_interval, $related_data, $extra_row);

                $data = array(
                    "type" => "reactive",
                    "subject" => trans("alerts.mail_reactive_subject"),
                    "title" => trans("alerts.detail_reactive_exp", array("sub_type"=>trans("alerts.capacitive"))),
                    "detail_exp" => trans('alerts.mail_reactive_detail_exp'),
                    "detail_info" => $detail_info,
                    "to" => $emails
                );

                self::sendEmail($data);
            }

            if(isset($detail["capacitive"]["action"]["sms"])){
                if( $detail["capacitive"]["sms"]["ratio"] > $ratio ){
                    $ratio = $detail["capacitive"]["sms"]["ratio"];
                }

                $body  = trans('alerts.mail_reactive_subject') . "\r\n";
                $body .= $client . "\r\n";
                $body .= trans('alerts.device') . ": " . $device_serial_no . " (".trans('global.'.$device_type).")" . "\r\n";
                $body .= trans('alerts.capacitive') . ": %" . number_format($detail["capacitive"]["email"]["ratio"],1);

                self::sendSms($body, $phones);
            }

            if( $detail["capacitive"]["notification"]["ratio"] > $ratio ){
                $ratio = $detail["capacitive"]["notification"]["ratio"];
            }

            $detail["ratio"] = $ratio;

            // An alert is always created
            self::createNotification("reactive", "capacitive", $device_id, json_encode($detail["capacitive"]));
        }
    }

    private static function currentController($definition_id, $definition_name, $policy, $device_id, $device_type, $modem_serial_no, $device_serial_no, $emails, $phones, $client, $distributor)
    {
        $policy = json_decode($policy);

        $table_name = "device_records_meter";
        if($device_type == "analyzer" || $device_type == "relay")
            $table_name = "device_records_modbus";

        $detail = array(
            "5A" => array(
                "definition_id" => $definition_id,
                "action" => array()
            ),
            "unbalanced" => array(
                "definition_id" => $definition_id,
                "action" => array()
            )
        );

        if( isset($policy->notification) ){
            if(self::isTimeToRun($device_id, "current", "notification", $policy->notification->notification_period)){
                self::currentAlertDetector($policy, "notification", $detail, $table_name, $modem_serial_no, $device_serial_no);
            }
        }

        if( isset($policy->email) ){
            if(self::isTimeToRun($device_id, "current", "email", $policy->email->notification_period)){
                self::currentAlertDetector($policy, "email", $detail, $table_name, $modem_serial_no, $device_serial_no);
            }
        }

        if(isset($policy->sms)){
            if(self::isTimeToRun($device_id,"current","sms",$policy->sms->notification_period)){
                self::currentAlertDetector($policy, "sms", $detail, $table_name, $modem_serial_no, $device_serial_no);
            }
        }

        //perform the actions
        if(COUNT($detail["5A"]["action"])>0){
            if( in_array("email", $detail["5A"]["action"]) ){
                $data_interval = date('d/m/Y H:i', strtotime($detail["5A"]["email"]["start_date"])) . "<br /> &nbsp;&nbsp;" . date('d/m/Y H:i', strtotime($detail["5A"]["email"]["end_date"]));

                $related_data = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.current_values')." </td>
                                <td style='color: #cc0000;'>
                                    <b> 
                                        &nbsp; L1: ". (isset($detail["5A"]["email"]["l1"])?number_format($detail["5A"]["email"]["l1"],2):'---') ." <br />
                                        &nbsp; L2: ". (isset($detail["5A"]["email"]["l2"])?number_format($detail["5A"]["email"]["l2"],2):'---') ." <br />
                                        &nbsp; L3: ". (isset($detail["5A"]["email"]["l3"])?number_format($detail["5A"]["email"]["l3"],2):'---') ."
                                    </b>
                                </td>
                            </tr>";

                $detail_info = self::prepareMailDetail($definition_name, $device_type, $device_serial_no, $client, $distributor, $data_interval, $related_data);

                $data = array(
                    "type" => "current",
                    "subject" => trans("alerts.mail_5A_subject"),
                    "title" => trans("alerts.detail_current_exp", array("sub_type"=>trans("alerts.higher_than_5A"))),
                    "detail_exp" => trans('alerts.mail_current_detail_exp', array("sub_type"=>trans("alerts.higher_than_5A"))),
                    "detail_info" => $detail_info,
                    "to" => $emails
                );

                self::sendEmail($data);
            }

            if( in_array("sms", $detail["5A"]["action"]) ){
                $current_values = "";

                if( array_key_exists("l1", $detail["5A"]["sms"]) ){
                    $current_values .= "L1: " . number_format($detail["5A"]["sms"]["l1"],2) . ", ";
                }

                if( array_key_exists("l2", $detail["5A"]["sms"]) ){
                    $current_values .= "L2: " . number_format($detail["5A"]["sms"]["l2"],2) . ", ";
                }

                if( array_key_exists("l3", $detail["5A"]["sms"]) ){
                    $current_values .= "L3: " . number_format($detail["5A"]["sms"]["l3"],2) . ", ";
                }

                $current_values = rtrim($current_values, ", ");

                $body  = trans('alerts.higher_than_5A') . "\r\n";
                $body .= $client . "\r\n";
                $body .= trans('alerts.device') . ": " . $device_serial_no . " (".trans('global.'.$device_type).")" . "\r\n";
                $body .= trans('alerts.current_values') . ": " . $current_values;

                self::sendSms($body, $phones);
            }

            // An alert is always created
            self::createNotification("current", "5A", $device_id, json_encode($detail["5A"]));
        }

        if(COUNT($detail["unbalanced"]["action"])>0){
            if( in_array("email", $detail["unbalanced"]["action"]) ){
                $data_interval = date('d/m/Y H:i', strtotime($detail["unbalanced"]["email"]["start_date"])) . "<br /> &nbsp;&nbsp;" . date('d/m/Y H:i', strtotime($detail["unbalanced"]["email"]["end_date"]));

                $related_data = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.current_values')." </td>
                                <td style='color: #cc0000;'>
                                    <b>
                                        &nbsp; L1: ". (isset($detail["unbalanced"]["email"]["l1"])?number_format($detail["unbalanced"]["email"]["l1"],2):'---') ." <br />
                                        &nbsp; L2: ". (isset($detail["unbalanced"]["email"]["l2"])?number_format($detail["unbalanced"]["email"]["l2"],2):'---') ." <br />
                                        &nbsp; L3: ". (isset($detail["unbalanced"]["email"]["l3"])?number_format($detail["unbalanced"]["email"]["l3"],2):'---') ."
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.occurrence_time')." </td>
                                <td> : <b>". date("d/m/Y H:i", strtotime($detail["unbalanced"]["email"]["date"]))."</b></td>
                            </tr>";

                $detail_info = self::prepareMailDetail($definition_name, $device_type, $device_serial_no, $client, $distributor, $data_interval, $related_data);

                $data = array(
                    "type" => "current",
                    "subject" => trans("alerts.mail_unbalanced_subject"),
                    "title" => trans("alerts.detail_current_exp", array("sub_type"=>trans("alerts.unbalanced_current"))),
                    "detail_exp" => trans('alerts.mail_current_detail_exp', array("sub_type"=>trans("alerts.unbalanced_current"))),
                    "detail_info" => $detail_info,
                    "to" => $emails
                );

                self::sendEmail($data);
            }

            if( in_array("sms", $detail["unbalanced"]["action"]) ){
                $current_values = "";

                if( array_key_exists("l1", $detail["unbalanced"]["sms"]) ){
                    $current_values .= "L1: " . number_format($detail["unbalanced"]["sms"]["l1"],2) . ", ";
                }

                if( array_key_exists("l2", $detail["unbalanced"]["sms"]) ){
                    $current_values .= "L2: " . number_format($detail["unbalanced"]["sms"]["l2"],2) . ", ";
                }

                if( array_key_exists("l3", $detail["unbalanced"]["sms"]) ){
                    $current_values .= "L3: " . number_format($detail["unbalanced"]["sms"]["l3"],2) . ", ";
                }

                $current_values = rtrim($current_values, ", ");

                $body  = trans('alerts.unbalanced_current') . "\r\n";
                $body .= $client . "\r\n";
                $body .= trans('alerts.device') . ": " . $device_serial_no . " (".trans('global.'.$device_type).")" . "\r\n";
                $body .= trans('alerts.current_values') . ": " . $current_values;

                self::sendSms($body, $phones);
            }

            // An alert is always created
            self::createNotification("current","unbalanced",$device_id,json_encode($detail["unbalanced"]));
        }
    }

    private static function voltageController($definition_id, $definition_name, $policy, $device_id, $device_type, $modem_serial_no, $device_serial_no, $emails, $phones, $client, $distributor)
    {
        $policy = json_decode($policy);

        $table_name = "device_records_meter";
        if($device_type == "analyzer" || $device_type == "relay")
            $table_name = "device_records_modbus";

        $detail = array(
            "lower" => array(
                "definition_id" => $definition_id,
                "action" => array()
            ),
            "upper" => array(
                "definition_id" => $definition_id,
                "action" => array()
            )
        );

        if(isset($policy->notification)){
            if(self::isTimeToRun($device_id,"voltage","notification",$policy->notification->notification_period)){
                self::voltageAlertDetector($policy,"notification",$detail,$table_name,$modem_serial_no,$device_serial_no);
            }
        }

        if(isset($policy->email)){
            if(self::isTimeToRun($device_id,"voltage","email",$policy->email->notification_period)){
                self::voltageAlertDetector($policy, "email", $detail, $table_name, $modem_serial_no, $device_serial_no);
            }
        }

        if(isset($policy->sms)){
            if(self::isTimeToRun($device_id,"voltage","sms",$policy->sms->notification_period)){
                self::voltageAlertDetector($policy, "sms", $detail, $table_name, $modem_serial_no, $device_serial_no);
            }
        }

        //perform the actions
        if(COUNT($detail["lower"]["action"])>0){
            if( in_array("email", $detail["lower"]["action"]) ){
                $data_interval = date('d/m/Y H:i', strtotime($detail["lower"]["email"]["start_date"])) . "<br /> &nbsp;&nbsp;" . date('d/m/Y H:i', strtotime($detail["lower"]["email"]["end_date"]));

                $extra_row = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.specified_lower_limit')." </td>
                                <td> : <b> ". $policy->email->voltage_lower_limit ." </b></td>
                            </tr>";

                $related_data = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.voltage_values')." </td>
                                <td style='color: #cc0000;'>
                                    <b> 
                                        &nbsp; L1: ". (isset($detail["lower"]["email"]["l1"])?number_format($detail["lower"]["email"]["l1"],2):'---') ." <br />
                                        &nbsp; L2: ". (isset($detail["lower"]["email"]["l2"])?number_format($detail["lower"]["email"]["l2"],2):'---') ." <br />
                                        &nbsp; L3: ". (isset($detail["lower"]["email"]["l3"])?number_format($detail["lower"]["email"]["l3"],2):'---') ."
                                    </b>
                                </td>
                            </tr>";

                $detail_info = self::prepareMailDetail($definition_name, $device_type, $device_serial_no, $client, $distributor, $data_interval, $related_data, $extra_row);

                $data = array(
                    "type" => "voltage",
                    "subject" => trans("alerts.mail_lower_voltage_subject"),
                    "title" => trans("alerts.detail_voltage_exp", array("sub_type"=>trans("alerts.mail_lower_voltage_subject"))),
                    "detail_exp" => trans('alerts.mail_current_detail_exp', array("sub_type"=>trans("alerts.mail_lower_voltage_subject"))),
                    "detail_info" => $detail_info,
                    "to" => $emails
                );

                self::sendEmail($data);
            }

            if( in_array("sms", $detail["lower"]["action"]) ){
                $voltage_values = "";

                if( array_key_exists("l1", $detail["lower"]["sms"]) ){
                    $voltage_values .= "L1: " . number_format($detail["lower"]["sms"]["l1"],0) . ", ";
                }

                if( array_key_exists("l2", $detail["lower"]["sms"]) ){
                    $voltage_values .= "L2: " . number_format($detail["lower"]["sms"]["l2"],0) . ", ";
                }

                if( array_key_exists("l3", $detail["lower"]["sms"]) ){
                    $voltage_values .= "L3: " . number_format($detail["lower"]["sms"]["l3"],0) . ", ";
                }

                $voltage_values = rtrim($voltage_values, ", ");

                $body  = trans('alerts.mail_lower_voltage_subject') . "\r\n";
                $body .= $client . "\r\n";
                $body .= trans('alerts.device') . ": " . $device_serial_no . " (".trans('global.'.$device_type).")" . "\r\n";
                $body .= trans('alerts.voltage_values') . ": " . $voltage_values;

                self::sendSms($body, $phones);
            }

            self::createNotification("voltage", "lower", $device_id, json_encode($detail["lower"]));
        }

        if(COUNT($detail["upper"]["action"])>0){
            if( in_array("email", $detail["upper"]["action"]) ){
                $data_interval = date('d/m/Y H:i', strtotime($detail["upper"]["email"]["start_date"])) . "<br /> &nbsp;&nbsp;" . date('d/m/Y H:i', strtotime($detail["upper"]["email"]["end_date"]));

                $extra_row = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.specified_upper_limit')." </td>
                                <td> : <b> ". $policy->email->voltage_upper_limit ." </b></td>
                            </tr>";

                $related_data = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.voltage_values')." </td>
                                <td style='color: #cc0000;'>
                                    <b> 
                                        &nbsp; L1: ". (isset($detail["upper"]["email"]["l1"])?number_format($detail["upper"]["email"]["l1"],2):'---') ." <br />
                                        &nbsp; L2: ". (isset($detail["upper"]["email"]["l2"])?number_format($detail["upper"]["email"]["l2"],2):'---') ." <br />
                                        &nbsp; L3: ". (isset($detail["upper"]["email"]["l3"])?number_format($detail["upper"]["email"]["l3"],2):'---') ."
                                    </b>
                                </td>
                            </tr>";

                $detail_info = self::prepareMailDetail($definition_name, $device_type, $device_serial_no, $client, $distributor, $data_interval, $related_data, $extra_row);

                $data = array(
                    "type" => "voltage",
                    "subject" => trans("alerts.mail_upper_voltage_subject"),
                    "title" => trans("alerts.detail_voltage_exp", array("sub_type"=>trans("alerts.mail_upper_voltage_subject"))),
                    "detail_exp" => trans('alerts.mail_current_detail_exp', array("sub_type" => trans("alerts.mail_upper_voltage_subject"))),
                    "detail_info" => $detail_info,
                    "to" => $emails
                );

                self::sendEmail($data);
            }

            if( in_array("sms", $detail["upper"]["action"]) ){
                $voltage_values = "";

                if( array_key_exists("l1", $detail["upper"]["sms"]) ){
                    $voltage_values .= "L1: " . number_format($detail["upper"]["sms"]["l1"],0) . ", ";
                }

                if( array_key_exists("l2", $detail["upper"]["sms"]) ){
                    $voltage_values .= "L2: " . number_format($detail["upper"]["sms"]["l2"],0) . ", ";
                }

                if( array_key_exists("l3", $detail["upper"]["sms"]) ){
                    $voltage_values .= "L3: " . number_format($detail["upper"]["sms"]["l3"],0) . ", ";
                }

                $voltage_values = rtrim($voltage_values, ", ");

                $body  = trans('alerts.mail_upper_voltage_subject') . "\r\n";
                $body .= $client . "\r\n";
                $body .= trans('alerts.device') . ": " . $device_serial_no . " (".trans('global.'.$device_type).")" . "\r\n";
                $body .= trans('alerts.voltage_values') . ": " . $voltage_values;


                self::sendSms($body, $phones);
            }

            self::createNotification("voltage", "upper", $device_id, json_encode($detail["upper"]));
        }
    }

    private static function connectionController($definition_id, $definition_name, $policy, $device_id, $modem_id, $device_type, $device_no, $last_date, $emails, $phones, $client, $distributor)
    {
        $policy = json_decode($policy);

        $start_date = date('Y-m-d 00:00:00', strtotime("-4 days"));
        $end_date = date("Y-m-d 23:59:59", strtotime("-1 days"));

        $hour = -1;
        $diff_verbal = trans('alerts.no_connection_yet');

        if( !is_null($last_date) && strtotime($end_date) >= strtotime($last_date) ){
            $last_data_diff = strtotime($end_date) - strtotime($last_date);
            $hour = round($last_data_diff / 3600);
            $diff_verbal = Helper::secondsToTime($last_data_diff);
        }

        $array_element = "modem";
        if( $device_type != "modem" )
            $array_element = "device";

        $detail = array(
            $array_element => array(
                "definition_id" => $definition_id,
                "diff" => $hour,
                "last_connection_date" => $last_date,
                "action" =>array()
            )
        );

        if( isset($policy->notification) ){
            if( self::isTimeToRun($device_id, "connection", "notification", $policy->notification->notification_period) ){
                $duration = $policy->notification->duration;

                if( $device_type == "modem" ){
                    // This means that check the connection has been disconnected for the specified time for modem
                    if( isset($policy->notification->modem_connection) && $policy->notification->modem_connection == 1 ){
                        if( is_null($last_date) || $hour > $duration ){
                            $detail["modem"]["action"][] = "notification";
                        }
                    }
                }
                else{
                    // This means that check the connection has been disconnected for the specified time for device
                    if( isset($policy->notification->device_connection) && $policy->notification->device_connection == 1 ){
                        if(  is_null($last_date) || $hour > $duration ){
                            $detail["device"]["action"][] = "notification";
                        }
                    }
                }
            }
        }

        if( isset($policy->email) ){
            if(self::isTimeToRun($device_id, "connection", "email", $policy->email->notification_period)){
                $duration = $policy->email->duration;

                if( $device_type == "modem" ){
                    // This means that check the connection has been disconnected for the specified time for modem
                    if( isset($policy->email->modem_connection) && $policy->email->modem_connection == 1 ){
                        if( is_null($last_date) || $hour > $duration ){
                            $detail["modem"]["action"][] = "email";
                        }
                    }
                }
                else{
                    // This means that check the connection has been disconnected for the specified time for device
                    if( isset($policy->email->device_connection) && $policy->email->device_connection == 1 ){
                        if( is_null($last_date) || $hour > $duration ){
                            $detail["device"]["action"][] = "email";
                        }
                    }
                }
            }
        }

        if( isset($policy->sms) ){
            if(self::isTimeToRun($device_id, "connection", "sms", $policy->sms->notification_period)){
                $duration = $policy->sms->duration;

                if( $device_type == "modem" ){
                    // This means that check the connection has been disconnected for the specified time for modem
                    if( isset($policy->sms->modem_connection) && $policy->sms->modem_connection == 1 ){
                        if( is_null($last_date) || $hour > $duration ){
                            $detail["modem"]["action"][] = "sms";
                        }
                    }
                }
                else{
                    // This means that check the connection has been disconnected for the specified time for device
                    if( isset($policy->sms->device_connection) && $policy->sms->device_connection == 1 ){
                        if( is_null($last_date) || $hour > $duration ){
                            $detail["device"]["action"][] = "sms";
                        }
                    }
                }
            }
        }

        //perform the actions
        if( $device_type == "modem" && COUNT($detail["modem"]["action"])>0 ){
            if( in_array("email", $detail["modem"]["action"]) ){
                $data_interval = date('d/m/Y H:i', strtotime($start_date)) . "<br /> &nbsp;&nbsp;" . date('d/m/Y H:i', strtotime($end_date));
                $extra_row = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.connection_alarm_limit')." </td>
                                <td> : <b> ".  $policy->email->duration ." ".trans('alerts.hour')."</b></td>
                            </tr>";

                $related_data = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.unconnected_time')." </td>
                                <td style='color: #cc0000;'>: <b>". $diff_verbal ." </b></td>
                            </tr>";

                $detail_info = self::prepareMailDetail($definition_name, $device_type, $device_no, $client, $distributor, $data_interval, $related_data, $extra_row);

                $data = array(
                    "type" => "connection",
                    "subject" => trans("alerts.mail_connection_subject"),
                    "title" => trans("alerts.detail_current_exp", array("sub_type"=>trans("alerts.mail_connection_subject"))),
                    "detail_exp" => trans('alerts.mail_current_detail_exp', array("sub_type"=>trans("alerts.mail_connection_subject"))),
                    "detail_info" => $detail_info,
                    "to" => $emails
                );

                self::sendEmail($data);
            }

            if( in_array("sms", $detail["modem"]["action"]) ){
                if( $diff_verbal != trans('alerts.no_connection_yet') ){
                    $diff_verbal .= " " . trans('alerts.before');
                }

                $body  = trans('alerts.mail_connection_subject') . "\r\n";
                $body .= $client . "\r\n";
                $body .= trans('alerts.modem_no') . ": " . $device_no . "\r\n";
                $body .= trans('alerts.last_connection_at') . ": " . $diff_verbal;

                self::sendSms($body, $phones);
            }

            self::createNotification("connection", "modem", $modem_id, json_encode($detail["modem"]));
        }
        else if( $device_type != "modem" && COUNT($detail["device"]["action"]) > 0 ){
            if( in_array("email", $detail["device"]["action"]) ){
                $data_interval = date('d/m/Y H:i', strtotime($start_date)) . "<br /> &nbsp;&nbsp;" . date('d/m/Y H:i', strtotime($end_date));

                $extra_row = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.connection_alarm_limit')." </td>
                                <td> : <b> ".  $policy->email->duration ." ".trans('alerts.hour')."</b></td>
                            </tr>";

                $related_data = "
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.unconnected_time')." </td>
                                <td style='color: #cc0000;'>: <b>". $diff_verbal ." </b></td>
                            </tr>";

                $detail_info = self::prepareMailDetail($definition_name, $device_type, $device_no, $client, $distributor, $data_interval, $related_data, $extra_row);

                $data = array(
                    "type" => "connection",
                    "subject" => trans("alerts.mail_connection_subject"),
                    "title" => trans("alerts.detail_current_exp", array("sub_type"=>trans("alerts.mail_connection_subject"))),
                    "detail_exp" => trans('alerts.mail_current_detail_exp', array("sub_type"=>trans("alerts.mail_connection_subject"))),
                    "detail_info" => $detail_info,
                    "to" => $emails
                );

                self::sendEmail($data);
            }

            if( in_array("sms", $detail["device"]["action"]) ){
                if( $diff_verbal != trans('alerts.no_connection_yet') ){
                    $diff_verbal .= " " . trans('alerts.before');
                }

                $body  = trans('alerts.mail_connection_subject') . "\r\n";
                $body .= $client . "\r\n";
                $body .= trans('alerts.device') . ": " . $device_no . " (". trans('global.'.$device_type).")" . "\r\n";
                $body .= trans('alerts.last_connection_at') . ": " . $diff_verbal;

                self::sendSms($body, $phones);
            }

            self::createNotification("connection", "device", $device_id, json_encode($detail["device"]));
        }
    }

    private static function reactiveAlertDetector($policy, $policy_type, &$detail, $table_name, $modem_serial_no, $device_serial_no,$invoice_day, $multiplier, $definition_id)
    {
        $inductive_limit = $policy->$policy_type->inductive_limit;
        $capacitive_limit = $policy->$policy_type->capacitive_limit;
        $calculation_period = $policy->$policy_type->calculation_period;
        $consumption_limit = $policy->$policy_type->consumption_limit;

        $invoice_day = ($invoice_day < 10 ? "0" : "") . $invoice_day;
        $start_date = date('Y-m-'.$invoice_day.' 00:00:00');
        $end_date = date("Y-m-d 23:59:59",strtotime("-1 days"));

        if(is_numeric($calculation_period) && $calculation_period > 0){
            $start_date = date('Y-m-d 00:00:00',strtotime('-'.$calculation_period.' days'));
        }

        $inductive_ratio = 0;
        $capacitive_ratio = 0;
        $active_consumption = 0;

        $reactive_result = DB::select('
                        SELECT  
                            MIN(positive_active_energy_total) as active_min, 
                            MAX(positive_active_energy_total) as active_max,
                            MAX(imported_inductive_reactive_energy_total_Q1) as inductive_max,
                            MIN(imported_inductive_reactive_energy_total_Q1) as inductive_min,
                            MAX(exported_capacitive_reactive_total_Q4) as capacitive_max,
                            MIN(exported_capacitive_reactive_total_Q4) as capacitive_min
                        FROM '.$table_name.' 
                        WHERE modem_serial_no=? 
                        AND device_serial_no=? 
                        AND server_timestamp 
                        BETWEEN ? AND ? ',
            array(
                $modem_serial_no,
                $device_serial_no,
                $start_date,
                $end_date
            )
        );

        if($reactive_result && COUNT($reactive_result)>0 && isset($reactive_result[0])){
            $reactive_result = $reactive_result[0];

            $active_consumption = ($reactive_result->active_max - $reactive_result->active_min)*$multiplier;
            $inductive_consumption = ($reactive_result->inductive_max - $reactive_result->inductive_min)*$multiplier;
            $capacitive_consumption = ($reactive_result->capacitive_max - $reactive_result->capacitive_min)*$multiplier;

            if($active_consumption>0){
                $inductive_ratio = ($inductive_consumption/$active_consumption)*100;
                $capacitive_ratio = ($capacitive_consumption/$active_consumption)*100;
            }
        }

        if( ($inductive_limit <= $inductive_ratio && $consumption_limit == 0) || ($inductive_limit <= $inductive_ratio && $consumption_limit <= $active_consumption) )
        {
            $detail["inductive"]["action"][] = $policy_type;
            $detail["inductive"][$policy_type] = array(
                "active_consumption" => $active_consumption,
                "ratio" => number_format($inductive_ratio,3),
                "limit" => $inductive_limit,
                "definition_id" => $definition_id,
                "start_date" => $start_date,
                "end_date" => $end_date,
            );
        }

        if( ($capacitive_limit <= $capacitive_ratio && $consumption_limit == 0) || ($capacitive_limit <= $capacitive_ratio && $consumption_limit <= $active_consumption) )
        {
            $detail["capacitive"]["action"][] = $policy_type;
            $detail["capacitive"][$policy_type] = array(
                "active_consumption" => $active_consumption,
                "ratio" => number_format($inductive_ratio,3),
                "limit" => $capacitive_limit,
                "definition_id" => $definition_id,
                "start_date" => $start_date,
                "end_date" => $end_date,
            );
        }
    }

    private static function currentAlertDetector($policy, $policy_type, &$detail, $table_name, $modem_serial_no,$device_serial_no)
    {
        $policy_arr = json_decode(json_encode($policy), true);
        $uc1 = 5;
        $uc2 = 1/$uc1;

        //this moment is the end date however start date is releted to notification_period
        $end_date = date("Y-m-d 23:59:59", strtotime("-1 days"));

        if($policy->$policy_type->notification_period->type == "periodic"){
            $start_date = date('Y-m-d 00:00:00', strtotime('-'.$policy->notification->notification_period->period.' days', strtotime(date("Y-m-d 00:00:00"))));
        }
        else if($policy->$policy_type->notification_period->type == "daily"){
            $start_date = date('Y-m-d 00:00:00', strtotime('-1 week', strtotime(date("Y-m-d 00:00:00"))));
        }
        else if($policy->$policy_type->notification_period->type == "definite_day"){
            $start_date = date('Y-m-d 00:00:00', strtotime('-1 month', strtotime(date("Y-m-d 00:00:00"))));
        }

        if( isset($policy_arr[$policy_type]["5A_current_status"]) && $policy_arr[$policy_type]["5A_current_status"] == 1 ){
            $result = DB::select("
                            SELECT 
                              MAX(instantaneous_current_L1 ) as max_l1, 
                              MAX(instantaneous_current_L2 ) as max_l2, 
                              MAX(instantaneous_current_L3 ) as max_l3 
                            FROM ".$table_name."
                            WHERE 
                                modem_serial_no=? AND 
                                device_serial_no=? AND 
                                server_timestamp BETWEEN ? AND ? 
                        ", array(
                                $modem_serial_no,
                                $device_serial_no,
                                $start_date,
                                $end_date
                            )
                        );

            if($result && COUNT($result)>0 && isset($result[0]->max_l1)){
                $result = $result[0];
                if($result->max_l1 > 5){
                    $detail["5A"][$policy_type]["l1"] = $result->max_l1;
                }

                if($result->max_l2 > 5){
                    $detail["5A"][$policy_type]["l2"] = $result->max_l2;
                }

                if($result->max_l3 > 5){
                    $detail["5A"][$policy_type]["l3"] = $result->max_l3;
                }

                if( array_key_exists($policy_type, $detail["5A"]) ){
                    $detail["5A"][$policy_type]["start_date"] = $start_date;
                    $detail["5A"][$policy_type]["end_date"] = $end_date;

                    $detail["5A"]["action"][] = $policy_type;
                }
            }
        }

        if( isset($policy_arr[$policy_type]["unbalanced_current_status"]) && $policy_arr[$policy_type]["unbalanced_current_status"] == 1 ){
            $result = DB::select("
                            SELECT 
                              server_timestamp,
                              instantaneous_current_L1 as l1, 
                              instantaneous_current_L2 as l2, 
                              instantaneous_current_L3 as l3 
                            FROM ".$table_name."
                            WHERE 
                                modem_serial_no=? AND 
                                device_serial_no=? AND 
                                server_timestamp BETWEEN ? AND ? AND 
                                (
                                    instantaneous_current_L1/instantaneous_current_L2 NOT BETWEEN ? AND ? OR
                                    instantaneous_current_L1/instantaneous_current_L3 NOT BETWEEN ? AND ? OR
                                    instantaneous_current_L2/instantaneous_current_L3 NOT BETWEEN ? AND ?
                                ) 
                            ORDER BY server_timestamp DESC 
                            LIMIT 1", array(
                                        $modem_serial_no,
                                        $device_serial_no,
                                        $start_date,
                                        $end_date,
                                        $uc2,
                                        $uc1,
                                        $uc2,
                                        $uc1,
                                        $uc2,
                                        $uc1
                                    )
                            );

            if($result && COUNT($result)>0 && isset($result[0]->server_timestamp)){
                $detail["unbalanced"][$policy_type]["start_date"] = $start_date;
                $detail["unbalanced"][$policy_type]["end_date"] = $end_date;

                $result = $result[0];

                $detail["unbalanced"][$policy_type]["l1"] = $result->l1;
                $detail["unbalanced"][$policy_type]["l2"] = $result->l2;
                $detail["unbalanced"][$policy_type]["l3"] = $result->l3;
                $detail["unbalanced"][$policy_type]["date"] = $result->server_timestamp;

                $detail["unbalanced"]["action"][] = $policy_type;
            }
        }
    }

    private static function voltageAlertDetector($policy, $policy_type, &$detail, $table_name, $modem_serial_no, $device_serial_no)
    {
        //this moment is the end date however start date is releted to notification_period
        $end_date = date("Y-m-d 23:59:59", strtotime("-1 days"));

        if($policy->$policy_type->notification_period->type == "periodic"){
            $start_date = date('Y-m-d H:i:s', strtotime('-'. $policy->notification->notification_period->period .' days', strtotime($end_date))); 
        }
        else if($policy->$policy_type->notification_period->type == "daily"){
            $start_date = date('Y-m-d 00:00:00', strtotime('-1 week', strtotime(date("Y-m-d 00:00:00"))));
        }
        else if($policy->$policy_type->notification_period->type == "definite_day"){
            $start_date = date('Y-m-d 00:00:00', strtotime('-1 month', strtotime(date("Y-m-d 00:00:00"))));
        }

        $result = DB::select("
                        SELECT 
                          MAX(instantaneous_voltage_L1) as max_l1, 
                          MAX(instantaneous_voltage_L2) as max_l2, 
                          MAX(instantaneous_voltage_L3) as max_l3, 
                          MIN(instantaneous_voltage_L1) as min_l1,
                          MIN(instantaneous_voltage_L2) as min_l2,
                          MIN(instantaneous_voltage_L3) as min_l3 
                        FROM ".$table_name."
                        WHERE 
                            modem_serial_no=? AND 
                            device_serial_no=? AND 
                            server_timestamp BETWEEN ? AND ? ",
                        array(
                            $modem_serial_no,
                            $device_serial_no,
                            $start_date,
                            $end_date
                        )
        );

        if($result && COUNT($result)>0 && isset($result[0]->max_l1)){
            $result = $result[0];

            if($result->min_l1 <= $policy->$policy_type->voltage_lower_limit){
                $detail["lower"][$policy_type]["l1"] = $result->min_l1;
            }

            if($result->min_l2 <= $policy->$policy_type->voltage_lower_limit){
                $detail["lower"][$policy_type]["l2"] = $result->min_l2;
            }

            if($result->min_l3 <= $policy->$policy_type->voltage_lower_limit){
                $detail["lower"][$policy_type]["l3"] = $result->min_l3;
            }

            if($result->max_l1 >= $policy->$policy_type->voltage_upper_limit){
                $detail["upper"][$policy_type]["l1"] = $result->max_l1;
            }

            if($result->max_l2 >= $policy->$policy_type->voltage_upper_limit){
                $detail["upper"][$policy_type]["l2"] = $result->max_l2;
            }

            if($result->max_l3 >= $policy->$policy_type->voltage_upper_limit){
                $detail["upper"][$policy_type]["l3"] = $result->max_l3;
            }

            if( array_key_exists($policy_type, $detail["lower"]) ){
                $detail["lower"][$policy_type]["start_date"] = $start_date;
                $detail["lower"][$policy_type]["end_date"] = $end_date;

                $detail["lower"]["action"][] = $policy_type;
            }

            if( array_key_exists($policy_type, $detail["upper"]) ){
                $detail["upper"][$policy_type]["start_date"] = $start_date;
                $detail["upper"][$policy_type]["end_date"] = $end_date;

                $detail["upper"]["action"][] = $policy_type;
            }
        }
    }

    private static function isTimeToRun($device_id, $alert_type, $notification_type, $notification_period){
        $period_type = $notification_period->type;
        $period = $notification_period->period;
        $return_value = false;

        $base_date = date("Y-m-d",strtotime("-1 days"));

        if($period_type == "periodic"){
            $result = DB::table('alert_period_track')
                ->where('device_id', $device_id)
                ->first();

            if($result && COUNT($result)>0 && is_numeric($result->id)){
                $dates = json_decode($result->dates);

                if(isset($dates->$alert_type->$notification_type)){
                    $last_date = $dates->$alert_type->$notification_type;

                    $datetime1 = date_create($last_date);
                    $datetime2 = date_create($base_date);
                    $interval = date_diff($datetime1, $datetime2);
                    $interval = $interval->days;

                    if($interval >= $period){
                        $return_value = true;

                        $dates->$alert_type->$notification_type = $base_date;
                        DB::table('alert_period_track')
                            ->where('device_id', $device_id)
                            ->update(
                                [
                                    "dates" => json_encode($dates)
                                ]
                            );
                    }
                }
                else{
                    $dates = json_decode($result->dates,true);
                    $dates[$alert_type][$notification_type] = $base_date;
                    DB::table('alert_period_track')->where('device_id', $device_id)
                        ->update(
                            [
                                "dates" => json_encode($dates)
                            ]
                        );
                }
            }
            else{
                $dates = array(
                    "".$alert_type => array(
                        "".$notification_type => $base_date
                    )
                );

                DB::table('alert_period_track')->insert(
                    [
                        "device_id" => $device_id,
                        "dates" => json_encode($dates)
                    ]
                );
            }
        }
        else if($period_type == "daily"){

            $base_day = strtolower(date("l",strtotime($base_date)));
            if($period == $base_day)
                $return_value =  true;
        }
        else if($period_type == "definite_day"){
            $base_day = strtolower(date("j",strtotime($base_date)));
            if($period == $base_day)
                $return_value =  true;
        }

        return $return_value;
    }

    private static function createNotification($type,$sub_type, $device_id,$detail){
        DB::table('alerts')->insert(
            [
                "type" => $type,
                "sub_type" => $sub_type,
                "device_id" => $device_id,
                "detail" => $detail
            ]
        );
    }

    private static function prepareMailDetail($definition_name, $device_type, $device_no, $client, $distributor, $data_interval, $related_data, $extra_row=false){

        $detail_info = "
                    <table cellpadding='1'>
                        <tbody>
                            <tr height='10px'></tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ". trans('alerts.device_type')." </td>
                                <td> : <b>". trans('global.'.$device_type) ." </b></td>
                            </tr> 
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.device_no')." </td>
                                <td> : <b>". $device_no ." </b></td>
                            </tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.client')." </td>
                                <td> : <b>". $client ." </b></td>
                            </tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.distributor')." </td>
                                <td> : <b>". $distributor ." </b></td>
                            </tr>
                            <tr height='10px'></tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.data_interval')." </td>
                                <td> : <b> ". $data_interval ." </b></td>
                            </tr>
                            <tr>
                                <td width='10px'></td>
                                <td> ".trans('alerts.alert_definition')." </td>
                                <td> : <b>". $definition_name ." </b></td>
                            </tr> 
                            ". ($extra_row != false ? $extra_row : '') ."
                            <tr height='10px'></tr>
                            ". $related_data ."
                            <tr height='10px'></tr>
                        </tbody>
                    </table>";

        return $detail_info;
    }

    private static function sendEmail($data){

        Mail::send("mail.alert", $data, function($message) use ($data) {
            //$message->to("abdulkadir.posul@gmail.com", env('MAIL_USERNAME'))->subject($data["subject"]);
            $message->to(json_decode($data["to"]))->subject($data["subject"]);
            $message->from(env('MAIL_USERNAME'), trans("global.mail_sender_name"));
        });

        if( count(Mail::failures())>0 ){
            return "ERROR";
        }
        else{
            return "SUCCESS";
        }
    }

    private static function sendSms($body, $phones){
        $phones = json_decode($phones);

        $phones = implode(",", $phones);

        Helper::sendSMS($body, $phones);
    }



}