<?php

namespace App\Http\Controllers;

use App\Models\DatabaseBackup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Yajra\DataTables\Facades\DataTables;

class DatabaseBackupController extends Controller
{
    public function index()
    {
        return view('database_backup.index');
    }
    public function dataTable()
    {
        $query = DatabaseBackup::with('user');
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(DatabaseBackup $databaseBackup) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('user_delete')) {
                    $btn .= ' <a role="button" data-id="' . $databaseBackup->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })
            ->addColumn('user_name', function(DatabaseBackup $databaseBackup) {
                return $databaseBackup->user->name ?? '';
            })
            ->addColumn('database_path', function(DatabaseBackup $databaseBackup) {
                if (file_exists(public_path($databaseBackup->database_path))){
                    return '<a download href="'.asset($databaseBackup->database_path).'" class="btn btn-success bg-gradient-success btn-xs"><i class="fa fa-download"></i></a>';
                }else{
                    return '<span class="badge badge-danger">File missing</span>';
                }
            })

            ->editColumn('created_at', function(DatabaseBackup $databaseBackup) {
                return Carbon::parse($databaseBackup->created_at)->format('d-m-Y, h:i:s a');
            })
            ->rawColumns(['action','database_path'])
            ->toJson();
    }

    public function create()
    {
        Artisan::call('database:backup');

        return redirect()->route('database-backups.index')->with('message','Database Backup Successful');
  }

    public function destroy(DatabaseBackup $database_backup)
    {
        try {
            if (!auth()->user()->hasPermissionTo('user_delete')) {
                abort(403, 'Unauthorized');
            }
            if (file_exists(public_path($database_backup->database_path))){
                unlink($database_backup->database_path);
            }
            $database_backup->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Database backup deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' =>$e->getMessage()], Response::HTTP_OK);
        }
    }

}
