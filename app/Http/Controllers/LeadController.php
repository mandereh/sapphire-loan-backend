<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LeadController extends Controller
{
    //
    public function uploadLeads(Request $request){
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv|max:2048',
        ]);
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();


        foreach ($worksheet->getRowIterator(2) as $row){
            $rowData = [];
            foreach ($row->getCellIterator() as $cell){
                $rowData[] = (string)$cell->getValue();
            }

            $lead = new Lead();
            $lead->name = $rowData[0];
            $lead->phone_number = $rowData[1];
            $lead->account_number = $rowData[2];
            $lead->ippis_number = $rowData[3];
            $lead->organization_name = $rowData[4];
            $lead->state_name = $rowData[5];
            $lead->city_name = $rowData[6];
            $lead->save();


        }
        return response()->json(['message' => 'Excel data imported successfully.']);

    }

    public function reassignLead(Request $request){
        $request->validate([
           'user_id' => 'required|integer',
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'integer|exists:leads,id'
        ]);
        if (!auth()->user()->can('reassign-leads')){
            abort(403, 'Unauthorized');
        }
        $user = auth()->user()->can('reassign-leads') ? auth()->user() : null;
        if (!$user) {
            return abort(403, 'Unauthorized');
        }
        $leads = Lead::whereIn('id', $request->lead_ids)->get();
        $leads->each(function ($lead) use ($user) {
            $lead->user_id = $user->id;
        });
        $leads->save();
        return response()->json(['message' => 'Leads reassigned successfully']);
    }

    public function deleteLeads(Request $request){
        $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'integer|exists:leads,id'
        ]);
        $user = auth()->user()->can('delete-leads') ? auth()->user() : null;
        if (!$user) {
            return abort(403, 'Unauthorized');
        }
        $leads = Lead::whereIn('id',$request->lead_ids)->delete();

        return response()->json(['message' => 'Leads deleted successfully']);
    }

    public function viewLeadsByAccountOfficer(){
        $user = auth()->user()->can('view-all-leads') ? auth()->user() : null;
        if (!$user){
            return abort(403, 'Unauthorized');
        }
        $leads = Lead::where('user_id', $user->id)->get();
        return response()->json($leads->toArray(), 200);
    }

    public function viewAllLeads(){
        $user = auth()->user() ? auth()->user() : null;
        if (!$user){
            return redirect()->route('login');
        }
        $leads = Lead::paginate(10);
        return response()->json($leads->toArray(), 200);
    }
}
