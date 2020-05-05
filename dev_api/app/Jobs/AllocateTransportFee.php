<?php

namespace App\Jobs;

use Exception;
use App\Jobs\Job;
use App\Models2\User;
use App\Models2\TransportVehicle;
use App\Models2\Transportation;
use App\Models2\payments;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class AllocateTransportFee implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;
    public $type;
    public $vehicle;
    public $date;
    public $dueDate;
    public $feeTitle;
    public $details;
    public $desc;
    public $fine;
    public $months;

    public function __construct( $type, $vehicle, $date, $dueDate, $feeTitle, $details, $desc, $fine, $months )
    {
        $this->type = $type;
        $this->vehicle = $vehicle;
        $this->date = $date;
        $this->dueDate = $dueDate;
        $this->feeTitle = $feeTitle;
        $this->details = $details;
        $this->desc = $desc;
        $this->fine = $fine;
        $this->months = $months;
    }

    public function handle()
    {
        $vehicle_id = $this->type;
        $vehicle = $this->vehicle;
        $date = $this->date;
        $dueDate = $this->dueDate;
        $feeTitle = $this->feeTitle;
        $details = $this->details;
        $desc = $this->desc;
        $fine = $this->fine;
        $months = $this->months;

        if( $vehicle_id != "all" && $vehicle )
        {
            User::$withoutAppends = true;
			$vehicle_stoppages = json_decode( $vehicle->stoppagesList, true );
			if( json_last_error() != JSON_ERROR_NONE ) $vehicle_stoppages = [];
			foreach( $vehicle_stoppages as $stopageItem  )
			{
				if( !array_key_exists('stoppage_id', $stopageItem) ) continue;
				if( !array_key_exists('fare', $stopageItem) ) continue;
				$stoppage_id = intval( $stopageItem['stoppage_id'] );
				$paymentAmount = $stopageItem['fare'];
				$paymentStudent = User::select('id', 'fullName as name')->where('transport_vehicle', $vehicle_id)->where('transport', $stoppage_id)->get()->toArray();
				if( !$paymentStudent ) { continue; }
				$paymentRows = []; $index = 0;
				foreach( $details as $detail )
				{
					$paymentRows[$index] = [ 'title' => $detail['title'], 'amount' => (string)$paymentAmount ];
					$index++;
				}
				if( count($paymentRows) != $months ) { continue; }
				foreach( $paymentStudent as $member )
				{
					$member_id = $member['id']; $name = $member['name'];
					if( $member_id == "" || $member_id == "0" || $member_id == 0 || $member_id == NULL) { continue; }
					$payments = new payments();
					$payments->paymentTitle = $feeTitle;
					if( $desc ) { $payments->paymentDescription = $desc; }
					$payments->paymentStudent = $member_id;
					$payments->paymentAmount = ( $paymentAmount * $months );
					$payments->paymentDiscounted = ( $paymentAmount * $months );
					$payments->paymentDate = $date;
					$payments->dueDate = $dueDate;
					$payments->paymentUniqid = uniqid();
					$payments->paymentStatus = 0;
					$payments->paymentRows = json_encode( $paymentRows );
					$payments->fine_amount = $fine;
					$payments->save();
					// user_log('Payments', 'create', 'Transportation Fee for: ' . $name);
				}
			}
        }
        else
        {
			User::$withoutAppends = true;
			$vehicles = TransportVehicle::select('*')->get()->toArray();
			foreach( $vehicles as $vehicle )
			{
				$vehicle_id = $vehicle['id'];
				$stoppagesList = json_decode( $vehicle['stoppagesList'], true );
				if( json_last_error() != JSON_ERROR_NONE ) $stoppagesList = [];
				foreach( $stoppagesList as $stopageItem )
				{
					if( !array_key_exists('stoppage_id', $stopageItem) ) continue;
					if( !array_key_exists('fare', $stopageItem) ) continue;
					$stoppage_id = intval( $stopageItem['stoppage_id'] );
					$paymentAmount = $stopageItem['fare'];
					$paymentStudent = User::select('id', 'fullName as name')->where('transport_vehicle', $vehicle_id)->where('transport', $stoppage_id)->get()->toArray();
					if( !$paymentStudent ) { continue; }
					$paymentRows = []; $index = 0;
                    foreach( $details as $detail )
					{
						$paymentRows[$index] = [ 'title' => $detail['title'], 'amount' => (string)$paymentAmount ];
						$index++;
					}
					if( count($paymentRows) != $months ) { continue; }
					foreach( $paymentStudent as $member )
					{
						$member_id = $member['id']; $name = $member['name'];
						if( $member_id == "" || $member_id == "0" || $member_id == 0 || $member_id == NULL) { continue; }
						$payments = new payments();
						$payments->paymentTitle = $feeTitle;
						if( $desc ) { $payments->paymentDescription = $desc; }
						$payments->paymentStudent = $member_id;
						$payments->paymentAmount = ( $paymentAmount * $months );
						$payments->paymentDiscounted = ( $paymentAmount * $months );
						$payments->paymentDate = $date;
						$payments->dueDate = $dueDate;
						$payments->paymentUniqid = uniqid();
						$payments->paymentStatus = 0;
						$payments->paymentRows = json_encode( $paymentRows );
						$payments->fine_amount = $fine;
						$payments->save();
						// user_log('Payments', 'create', 'Transportation Fee for: ' . $name);
					}
				}
			}
        }
    }

    public function failed(Exception $exception)
    {
        \Log::error($exception->getMessage());
    }
}