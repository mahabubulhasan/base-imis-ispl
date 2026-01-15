<?php
namespace App\Services\Fsm;

use App\Models\BuildingInfo\Building;
use App\Models\Fsm\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PublicApplicationService
{

    private function getValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'holding_owner_name' => 'nullable|string|max:255',
            'ward' => 'required|string',
            'road_code' => 'nullable|string|max:255',
            'tax_id' => [
                'nullable',
                'string',
                'max:50',
            ],
            'address' => 'required|string|max:500',
            'proposed_emptying_date' => 'required|date|after_or_equal:today',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'customer_name.required' => 'Customer Name is required.',
            'customer_contact.required' => 'Contact No. is required.',
            'ward.required' => 'Ward is required.',
            'tax_id.required' => 'Tax ID is required.',
            'address.required' => 'Address is required.',
            'proposed_emptying_date.required' => 'Proposed Emptying Date is required.',
            'proposed_emptying_date.after_or_equal' => 'Proposed Emptying Date must be today or a future date.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ]);
    }

    private function getApplicationStatus($containment_id)
    {
        return Application::where('containment_id', $containment_id)
            ->where('emptying_status', false)
            ->whereNull('deleted_at')
            ->exists();
    }

    private function getBuilding($tax_code)
    {
        return Building::where('tax_code', $tax_code)->first();
    }

    private function getContainmentId(Building $building)
    {
        $firstContainment = $building->containments()->first();
        return $firstContainment?->pivot?->containment_id;
    }

    public function createApplication(Request $request)
    {
        $validator = $this->getValidator($request);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $building = $request->tax_id ? $this->getBuilding($request->tax_id) : null;
        $containment_id = $building ? $this->getContainmentId($building) : null;

        if($containment_id && $this->getApplicationStatus($containment_id))
        {
            return redirect()->back()->withInput()->with('error',"Error! Containment already has running Application.");
        }

        try {
            $application = Application::create($request->all());
            $application->containment_id = $containment_id;
            
            if ($building) {
                $application->bin = $building->bin;
            }
            
            $application->road_code = $request->road_code;
            $application->proposed_emptying_date = $request->proposed_emptying_date;
            $application->address = $request->address;

            $owner = $building ? $building->owners : null;
            
            $ownerName = $owner ? $owner->owner_name : null;
            $ownerContact = $owner ? $owner->owner_contact : null;
            $ownerGender = $owner ? $owner->owner_gender : null;

            $application->customer_name = $request->holding_owner_name ?? $request->customer_name ?? $ownerName;
            $application->customer_contact = $request->customer_contact ?? $ownerContact;
            $application->customer_gender = $ownerGender;

            $application->application_date = now()->format('Y-m-d H:i:s');
            $application->applicant_name = $request->customer_name ?? $ownerName ?? null;
            $application->applicant_contact = $request->customer_contact ?? $ownerContact ?? null;
            $application->save();
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', "Error! Application couldn't be created.");
        }

        return redirect(route('client-fsm-application.form'))->with('success', 'Your application submitted successfully, Thank You.');
    }
}
