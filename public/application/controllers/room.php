<?php

class Room extends MY_Controller 
{       
	function __construct()
	{
		parent::__construct();
		
		$this->load->model('Company_model');
		$this->load->model('Room_model');
		$this->load->model('Room_type_model');
		$this->load->model('User_model');	
		$this->load->model('Channel_model');	
		$this->load->model('Date_range_model');
        $this->load->model('Date_color_model');
        $this->load->model('Employee_log_model');
        
		$global_data['menu_on'] = true;			
		$global_data['submenu'] = 'includes/submenu.php';
		$global_data['submenu_parent_url'] = base_url()."room/";
        $company_partner_id = isset($this->company_partner_id) && $this->company_partner_id ? $this->company_partner_id : 1;
        $global_data['menu_items'] = $this->Menu_model->get_menus(array('parent_id' => 3, 'wp_id' => $company_partner_id));
        
		$this->load->vars($global_data);
		
		$this->load->helper('url');
		
	}
	
	function index() {

		$data['js_files'] = array(
			base_url() . auto_version('js/rooms.js'),
			base_url() . auto_version('js/booking/booking_main.js'),
			base_url() . auto_version('js/booking/booking_list.js'),
			base_url() . auto_version('js/room_status.js')
		);		

		$room_ratings = $this->Room_model->get_room_rating($this->company_id);
		$data['date'] = $this->selling_date;
		$data['rows'] = $this->Room_model->get_room_inventory($data['date'], false);

		for($i = 0; $i < count($data['rows']); $i++)
		{
			for($j = 0; $j < count($room_ratings); $j++)
			{
				if($room_ratings[$j]['room_id'] == $data['rows'][$i]->room_id)
				{
					$data['rows'][$i]->rating = $room_ratings[$j]['average_rating'];
					$data['rows'][$i]->total_ratings = $room_ratings[$j]['total_ratings'];
					$data['rows'][$i]->total_reviews = $room_ratings[$j]['total_reviews'];
				}
			}
		}
		
		// load content
		$data['selected_menu'] = 'rooms';			
		$data['main_content'] = 'room/room_status';
		$data['selected_submenu'] = 'Status';
		$this->load->view('includes/bootstrapped_template', $data);			
	}

	function update_room_status()
    {	
		$room_status = sqli_clean($this->security->xss_clean($this->input->post('room_status', TRUE)));
		$room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
		$this->Room_model->update_room_status($room_id, $room_status);                
        echo json_encode($room_status);
    }

    function update_room_score()
    {	
		$room_score = sqli_clean($this->security->xss_clean($this->input->post('room_score', TRUE)));
		$room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
		$this->Room_model->update_room_score($room_id, $room_score);                
        echo json_encode(array('success' => true, 'score' => $room_score));
    }
	
	function set_rooms_clean()
	{
		$this->Room_model->set_rooms_clean($this->company_id);
	}	
			
	function view_room_note_form($room_id, $loading_for_first_time=0) {
		
		// if loading for the first time, load from DB, otherwise, use pre-submit data
		if ($loading_for_first_time) {
			$room_data = $this->Room_model->get_room($room_id);			
		} else {
			$room_data = $this->_parse_room_note_fields(); // this one doesn't actually get used in any models	
		}
		
		// form requires validation
		$input = $this->input->post('submit');
		switch($input) 
		{				
			case('save'):
				echo 	"<script type='text/javascript'>
							alert('Saved Successfully');					
						</script>";
				$this->Room_model->update_room($room_id, $room_data); // save booking detail information
                $this->_create_room_log("Room Updated (".json_encode($room_data)." [ID $room_id])");
		}
		$this->load->view('room/room_note_form', $room_data);
	}

    function _parse_room_note_fields()
    {
        $data['room_id'] = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
        $data['notes']   = sqli_clean($this->security->xss_clean($this->input->post('notes', TRUE)));

        return $data;
    }
	
	function _parse_room_edit_fields() {
		$data['room_id'] = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
		$data['room_name'] = sqli_clean($this->security->xss_clean($this->input->post('room_name', TRUE)));
		$data['room_type_id'] = sqli_clean($this->security->xss_clean($this->input->post('room_type_id', TRUE)));
		return $data;
	}

	function get_room_AJAX($room_id)
	{
		$response = $this->Room_model->get_room($room_id);
		echo json_encode($response);
	}

	function get_room_reviews()
	{
		$room_id = $this->input->post('room_id');
		$response = $this->Room_model->get_room_reviews($room_id);
		foreach($response as $key => $value)
		{
			if(isset($value['created']) && $value['created'])
				$response[$key]['created'] = date("F jS, Y", strtotime($value['created']));
			else
				$response[$key]['created'] = date("F jS, Y", strtotime($value['check_out_date']));
		}
		echo json_encode($response);
	}

	function update_room_AJAX()
	{
		$room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id', TRUE)));
		
		// we no longer allow user to update room name from calendar page
		$data = Array(
			//'room_name' => $this->input->post('room_name'),
			//'room_type_id' => $this->input->post('room_type_id'),
			'status' => sqli_clean($this->security->xss_clean($this->input->post('status', TRUE)))
		);

		$this->Room_model->update_room($room_id, $data);
        //$this->_create_room_log("Room updated ({$data['room_name']} [{$room_id}])");

		$response = array(
		  'response'=>'success!'
		);

		echo json_encode($response);

	}	
    
	function _create_room_log($log) {
        $log_detail =  array(
                    "user_id" => $this->user_id,
                    "selling_date" => $this->selling_date,
                    "date_time" => gmdate('Y-m-d H:i:s'),
                    "log" => $log,
                );   
        
        $this->Employee_log_model->insert_log($log_detail);     
    }
    
	function get_notes_AJAX()
	{
		$room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id')));
		$response = array(
		  'response'=>'success!',
		  'notes' => $this->Room_model->get_notes($room_id)
		);
		echo json_encode($response);

	}

	function update_notes_AJAX()
	{
		$room_id = $this->input->post('room_id');
		$notes = $this->input->post('notes');

		$data = Array(
			"notes" => $notes
			);

		$this->Room_model->update_room($room_id, $data);
        $this->_create_room_log("Room Note Updated ([ID $room_id])");
		$response = array(
		  'response'=>'success!'
		);

		echo json_encode($response);

	}

	// net availability
	function get_room_type_availability_AJAX()
	{
        $channel = sqli_clean($this->security->xss_clean($this->input->get('channel')));
        $start_date = sqli_clean($this->security->xss_clean($this->input->get('start_date')));
        $end_date = sqli_clean($this->security->xss_clean($this->input->get('end_date')));
		$filter_can_be_sold_online = $this->input->get('filter_can_be_sold_online') == 'true' ? true : false;
		$res = $this->Room_type_model->get_room_type_availability($this->company_id, $channel, $start_date, $end_date, null, null, $filter_can_be_sold_online);
        $data_ar = array();
        if(!empty($res)){
            foreach($res as $key => $r){
                $result = $this->Room_model->get_room_count_by_room_type_id($r['id']); //  get rooms in particular room type
                $res[$key]['room_count'] = $result['room_count'];
            }
        }
		echo json_encode($res, true);
	}
       
    function get_rooms_available_AJAX()
	{
		$channel = sqli_clean($this->security->xss_clean($this->input->get('channel', TRUE)));
		$start_date =  sqli_clean($this->security->xss_clean($this->input->get('start_date', TRUE)));
		$end_date =  sqli_clean($this->security->xss_clean($this->input->get('end_date', TRUE)));
		$company_id = sqli_clean($this->security->xss_clean($this->input->get('company_id', TRUE)));
		$res = $this->Room_type_model->get_room_type_availability($company_id, $channel, $start_date, $end_date);
		$data_ar = array();
		$result = null;
		if(!empty($res)){
			foreach($res as $key => $r){

				$result = $this->Room_model->get_room_count_by_room_type_id($r['id']); //  get rooms in particular room type
				$res[$key]['room_count'] = $result['room_count'];

				if($result['room_count'] > 0)
				{
					$data_ar[$key] = array();
					foreach($r['availability'] as $row)
					{
						if($row['availability'] == '0')
						{
							$check_in_date = ($row['date_start']);
							$check_in_date_object = date_create($row['date_start']);
							$check_out_date_object = date_create($row['date_end']);

							$interval = date_diff($check_in_date_object, $check_out_date_object);

							$days = $interval->format('%a');

							for($i = 0; $i <= ($days - 1)  ; $i++)
							{
								$data_ar[$key][] = $check_in_date;
								$check_in_date = date('Y-m-d',strtotime($check_in_date . "+1 days"));
							}
						}
					}                        
				}

			} 
			$result = null;
			foreach($data_ar as $data){
				if($result !== null){
					$result = array_intersect($data, $result);
				}else{
					$result = $data;
				}
			}
		}
		echo json_encode($result ? $result : array());
	}

	// max availability set by hotels
	function get_room_type_max_availability_AJAX()
	{
        $channel = sqli_clean($this->security->xss_clean($this->input->get('channel')));
        $start_date = sqli_clean($this->security->xss_clean($this->input->get('start_date')));
        $end_date = sqli_clean($this->security->xss_clean($this->input->get('end_date')));
		$res = $this->Room_type_model->get_room_type_max_availability($this->company_id, $channel, $start_date, $end_date);
        echo json_encode($res, true);
	}
	
    function get_instructions_AJAX()
    {
        $room_id = sqli_clean($this->security->xss_clean($this->input->post('room_id')));
        $response = array(
          'response'=>'success!',
          'instructions' => $this->Room_model->get_instructions($room_id)
        );
        echo json_encode($response);
    }

    function update_instructions_AJAX()
    {
        $room_id = $this->input->post('room_id');
        $instructions = $this->input->post('instructions');

        $data = Array(
            "instructions" => $instructions
            );

        $this->Room_model->update_room($room_id, $data);
        $this->_create_room_log("Room Instruction Updated ([ID $room_id])");
        $response = array(
          'response'=>'success!'
        );

        echo json_encode($response);
    }
}