<?php

class Email_template
{
    
    public function __construct()
    {
        $this->ci =& get_instance();
        
		$this->ci->load->model('Booking_model');
		$this->ci->load->model('Booking_room_history_model');
		$this->ci->load->model('Room_model');
		$this->ci->load->model('Room_type_model');
		$this->ci->load->model('Rate_model');
		$this->ci->load->model('Customer_model');
		$this->ci->load->model('Company_model');
		$this->ci->load->library('Email');
		$this->ci->load->helper('language_translation_helper');
		
		log_message('debug', "Email template initialized");
    }

    function send_booking_confirmation_email($booking_id, $booking_type = "new"){
    	$booking_data = $this->ci->Booking_model->get_booking($booking_id);

		$company_id = $booking_data['company_id'];
		$company = $this->ci->Company_model->get_company_data($company_id);

		$booking_room_history_data = $this->ci->Booking_room_history_model->get_block($booking_id);
		
		$room_data = $this->ci->Room_model->get_room($booking_room_history_data['room_id']);
		$customer_data['staying_customers'] = $this->ci->Booking_model->get_booking_staying_customers($booking_id, $company_id);
		

		$number_of_nights = (strtotime($booking_room_history_data['check_out_date']) - strtotime($booking_room_history_data['check_in_date']))/(60*60*24);
		if ($booking_data['use_rate_plan'] == '1')
        {
            $this->ci->load->library('Rate');
            $rate_array = $this->ci->rate->get_rate_array(
                $booking_data['rate_plan_id'],
                date('Y-m-d', strtotime($booking_room_history_data['check_in_date'])),
                date('Y-m-d', strtotime($booking_room_history_data['check_out_date'])),
                $booking_data['adult_count'],
                $booking_data['children_count']
            );

            $rate_plan   = $this->ci->Rate_plan_model->get_rate_plan($booking_data['rate_plan_id']);

            $tax_rates = $this->ci->Charge_type_model->get_taxes($rate_plan['charge_type_id']);

            $charge_type_id = $rate_plan['charge_type_id'];

            foreach ($rate_array as $index => $rate)
            {
                $tax_total = 0;
                if($tax_rates && count($tax_rates) > 0)
                {
                    foreach($tax_rates as $tax){
                        if($tax['is_tax_inclusive'] == 0){
                            $tax_total += (($tax['is_percentage'] == '1') ? ($rate_array[$index]['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                        }
                    }
                }
                $total_charges += $rate_array[$index]['rate'];
                $total_charges_with_taxes += $rate_array[$index]['rate'] + $tax_total;
            }
            $rate = $total_charges;
            $rate_with_taxes = $total_charges_with_taxes;
        }	
		else
		{
			$rate = $booking_data['rate'] * $number_of_nights;
		}

		$customer_info = $this->ci->Customer_model->get_customer($booking_data['booking_customer_id']);				
		$room_type = $this->ci->Room_type_model->get_room_type($room_data['room_type_id']);	

		//Send confirmation email
		$email_data = array (					
			'booking_id' => $booking_id,
			
			'customer_name' => $customer_info['customer_name'],
			
			'customer_address' => $customer_info['address'],
			'customer_city' => $customer_info['city'],
			'customer_region' => $customer_info['region'],
			'customer_country' => $customer_info['country'],
			'customer_postal_code' => $customer_info['postal_code'],
			
			'customer_phone' => $customer_info['phone'],
			'customer_email' => $customer_info['email'],
			
			'check_in_date' => $booking_room_history_data['check_in_date'],
			'check_out_date' => $booking_room_history_data['check_out_date'],
			
			'room_type' => $room_type['name'],
			
			'rate' => $rate,
			'average_daily_rate' => $booking_data['rate'],
			'company_name' => $company['name'],
			'charge_type_id' => $charge_type_id,
			
			'company_address' => $company['address'],
			
			'company_city' => $company['city'],
			'company_region' => $company['region'],
			'company_country' => $company['country'],
			'company_postal_code' => $company['postal_code'],
			
			'company_phone' => $company['phone'],
			'company_email' => $company['email'],
			'company_website' => $company['website'],
			'company_fax' => $company['fax'],
			'reservation_policies' => $company['reservation_policies'],
			'booking_confirmation_email_header' => $company['booking_confirmation_email_header'],
			'amount_due' => $booking_data['balance'],
            'rate_plan_detail' => $rate_plan,
            'rate_with_taxes' => $rate_with_taxes
		);

		// customer doesn't have email entered
		if ($email_data['customer_email'] == null || strlen($email_data['customer_email']) <= 1) {
			return;
		}

		$email_from = isset($company['avoid_dmarc_blocking']) && $company['avoid_dmarc_blocking'] ? 'sender@ixbt.media' : $company['email'];

		$this->ci->email->from($email_from, $company['name']);
		
		// don't send emails unless in production environment
        if (strtolower($_SERVER['HTTP_HOST']) != 'api.roomsy.com')
        {
        	if (isset($email_data['customer_email']))
        	{
        		$this->ci->email->to($email_data['customer_email']);	
        	}   
        }
        else
        {
        	$this->ci->email->to("booking@ixbt.media");
        }
        
		$this->ci->email->reply_to($email_data['company_email']);
		
		$this->ci->email->subject($email_data['company_name'] . ' - '.$booking_type.' Booking Confirmation: ' . $email_data['booking_id']);
		$this->ci->email->message($this->ci->load->view('email/new_booking_confirm-html', $email_data, true));

		$this->ci->email->send();


		return array(
            "success" => true,
            "message" => "Email successfully sent to ".$email_data['customer_email'],
            "customer_email" => $email_data['customer_email']
        );
    }

    function send_booking_alert_email($booking_id, $booking_type = "new"){
    	$booking_data = $this->ci->Booking_model->get_booking($booking_id);

		$company_id = $booking_data['company_id'];
		$company = $this->ci->Company_model->get_company_data($company_id);

		$booking_room_history_data = $this->ci->Booking_room_history_model->get_block($booking_id);
		
		$room_data = $this->ci->Room_model->get_room($booking_room_history_data['room_id']);
		$customer_data['staying_customers'] = $this->ci->Booking_model->get_booking_staying_customers($booking_id, $company_id);
		

		$number_of_nights = (strtotime($booking_room_history_data['check_out_date']) - strtotime($booking_room_history_data['check_in_date']))/(60*60*24);
		if ($booking_data['use_rate_plan'] == '1')
        {
            $this->ci->load->library('Rate');
            $rate_array = $this->ci->rate->get_rate_array(
                $booking_data['rate_plan_id'],
                date('Y-m-d', strtotime($booking_room_history_data['check_in_date'])),
                date('Y-m-d', strtotime($booking_room_history_data['check_out_date'])),
                $booking_data['adult_count'],
                $booking_data['children_count']
            );

            $rate_plan   = $this->ci->Rate_plan_model->get_rate_plan($booking_data['rate_plan_id']);

            $tax_rates = $this->ci->Charge_type_model->get_taxes($rate_plan['charge_type_id']);

            $charge_type_id = $rate_plan['charge_type_id'];

            foreach ($rate_array as $index => $rate)
            {
                $tax_total = 0;
                if($tax_rates && count($tax_rates) > 0)
                {
                    foreach($tax_rates as $tax){
                        if($tax['is_tax_inclusive'] == 0){
                            $tax_total += (($tax['is_percentage'] == '1') ? ($rate_array[$index]['rate'] * $tax['tax_rate'] / 100) : $tax['tax_rate']);
                        }
                    }
                }
                $total_charges += $rate_array[$index]['rate'];
                $total_charges_with_taxes += $rate_array[$index]['rate'] + $tax_total;
            }
            $rate = $total_charges;
            $rate_with_taxes = $total_charges_with_taxes;
        }	
		else
		{
			$rate = $booking_data['rate'] * $number_of_nights;
		}

		$customer_info = $this->ci->Customer_model->get_customer($booking_data['booking_customer_id']);				
		$room_type = $this->ci->Room_type_model->get_room_type($room_data['room_type_id']);	

		//Send confirmation email
		$email_data = array (					
			'booking_id' => $booking_id,
			
			'customer_name' => $customer_info['customer_name'],
			
			'customer_address' => $customer_info['address'],
			'customer_city' => $customer_info['city'],
			'customer_region' => $customer_info['region'],
			'customer_country' => $customer_info['country'],
			'customer_postal_code' => $customer_info['postal_code'],
			
			'customer_phone' => $customer_info['phone'],
			'customer_email' => $customer_info['email'],
			
			'check_in_date' => $booking_room_history_data['check_in_date'],
			'check_out_date' => $booking_room_history_data['check_out_date'],
			
			'room_type' => $room_type['name'],
			
			'rate' => $rate,
			'average_daily_rate' => $booking_data['rate'],
			'company_name' => $company['name'],
			'charge_type_id' => $charge_type_id,
			
			'company_address' => $company['address'],
			
			'company_city' => $company['city'],
			'company_region' => $company['region'],
			'company_country' => $company['country'],
			'company_postal_code' => $company['postal_code'],
			
			'company_phone' => $company['phone'],
			'company_email' => $company['email'],
			'company_website' => $company['website'],
			'company_fax' => $company['fax'],
			'reservation_policies' => $company['reservation_policies'],
			'booking_confirmation_email_header' => $company['booking_confirmation_email_header'],
			'amount_due' => $booking_data['balance'],
            'rate_plan_detail' => $rate_plan,
            'rate_with_taxes' => $rate_with_taxes
		);

		// customer doesn't have email entered
		if ($email_data['customer_email'] == null || strlen($email_data['customer_email']) <= 1) {
			return;
		}

		$email_from = isset($company['avoid_dmarc_blocking']) && $company['avoid_dmarc_blocking'] ? 'sender@ixbt.media' : $company['email'];

		$this->ci->email->from($email_from, $company['name']);
		
		// don't send emails unless in production environment
        if (strtolower($_SERVER['HTTP_HOST']) != 'api.roomsy.com')
        {
        	if (isset($email_data['company_email']))
        	{
        		$this->ci->email->to($email_data['company_email']);	
        	}   
        }
        else
        {
        	$this->ci->email->to("booking@ixbt.media");
        }
        
		$this->ci->email->reply_to($email_data['company_email']);
		
		$this->ci->email->subject($email_data['company_name'] . ' - '.$booking_type.' Booking Confirmation: ' . $email_data['booking_id']);
		$this->ci->email->message($this->ci->load->view('email/new_booking_confirm-html', $email_data, true));

		$this->ci->email->send();

		return array(
            "success" => true,
            "message" => "Email successfully sent to ".$email_data['customer_email'],
            "customer_email" => $email_data['customer_email']
        );
    }

    function send_overbooking_email($booking_id, $is_non_continuous_available = true, $room_type_availability = null, $no_rooms_available = false)
	{		

		$booking_data = $this->ci->Booking_model->get_booking($booking_id);
		$company_id = $booking_data['company_id'];
		$company = $this->ci->Company_model->get_company_data($company_id);

		$this->set_language($company['default_language']);

		$booking_room_history_data = $this->ci->Booking_room_history_model->get_block($booking_id);
		$room_data = $this->ci->Room_model->get_room($booking_room_history_data['room_id']);

		$customer_info = $this->ci->Customer_model->get_customer($booking_data['booking_customer_id']);				
		$room_type = $this->ci->Room_type_model->get_room_type($room_data['room_type_id']);				

		//Send confirmation email
		$email_data = array (					
			'booking_id' => $booking_id,
			
			'customer_name' => $customer_info['customer_name'],
			
			'customer_address' => $customer_info['address'],
			'customer_city' => $customer_info['city'],
			'customer_region' => $customer_info['region'],
			'customer_country' => $customer_info['country'],
			'customer_postal_code' => $customer_info['postal_code'],
			
			'customer_phone' => $customer_info['phone'],
			'customer_email' => $customer_info['email'],
			
			'check_in_date' => $booking_room_history_data['check_in_date'],
			'check_out_date' => $booking_room_history_data['check_out_date'],
			
			'room_type' => $room_type['name'],
			'room' 		=> $room_data['room_name'],
			'source'	=> $booking_data['source'],
			
			'company_name' => $company['name'],
			'allow_non_continuous_bookings' => $company['allow_non_continuous_bookings'],
			'is_non_continuous_available' => $is_non_continuous_available,
			'room_type_availability' => $room_type_availability,
			'no_rooms_available' => $no_rooms_available
		);

		$this->ci->email->from('sender@ixbt.media');
		

		// don't send emails unless in production environment
        if (strtolower($_SERVER['HTTP_HOST']) == 'api.roomsy.com')
        {
        	if (isset($company['email']))
        	{
        		$this->ci->email->to($company['email']);
                $this->ci->email->cc('booking@ixbt.media');
        	}   
        }
        else
        {
        	$this->ci->email->to('booking@ixbt.media');
        }

		$this->ci->email->reply_to('booking@ixbt.media');
		
		$this->ci->email->subject('Overbooking alert | ' . $email_data['company_name']);
		$this->ci->email->message($this->ci->load->view('email/overbooking-html', $email_data, true));

		$this->ci->email->send();

		$this->reset_language($company['default_language']);

		return array('success' => true, 'owner_email' => $company['email']);
	}
}
