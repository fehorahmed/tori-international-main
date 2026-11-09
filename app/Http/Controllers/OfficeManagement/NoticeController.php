<?php

namespace App\Http\Controllers\OfficeManagement;

use App\Http\Controllers\Controller;
use App\Models\OfficeManagement\Notice;
use App\Models\User;
use App\Notifications\NoticePosted;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Yajra\DataTables\Facades\DataTables;

class NoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('office_management.notice.index');
    }

    public function show(Notice $notice,Request $request)
    {
        readNotification($request->notification_id);
        return view('office_management.notice.show',compact('notice'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('notice_create')) {
            abort(403, 'Unauthorized');
        }
        return view('office_management.notice.create');
    }
    public function dataTable()
    {
        $query = Notice::where('sister_concern_id',auth()->user()->activeConcernId());
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Notice $notice) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('notice_edit')) {
                    $btn .= '<a href="' . route('notices.edit', ['notice' => $notice->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('notice_view')) {
                    $btn .= ' <a href="' . route('notices.show', ['notice' => $notice->id]) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('notice_delete')) {
                    $btn .= ' <a role="button" data-id="' . $notice->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })
            ->editColumn('content', function(Notice $notice) {

                return $notice->content;
            })
            ->editColumn('is_urgent', function(Notice $notice) {

                if ($notice->is_urgent == 1){
                    return '<span class="badge badge-success">Yes</span>';
                }

                return '<span class="badge badge-info">No</span>';

            })

            ->rawColumns(['action','is_urgent','content'])
            ->toJson();
    }
    /**
     * Store a newly created resource in storage.
     */
    // Function to handle images in the content
    protected function handleImagesInContent($content)
    {
        // Use regex to find base64 images in the content
        preg_match_all('/<img[^>]+src="data:image\/([^;]+);base64,([^"]+)"/', $content, $matches);

        // If there are matches (base64 images) found
        if (isset($matches[0]) && count($matches[0]) > 0) {
            foreach ($matches[0] as $index => $imgTag) {
                // Get the image extension (e.g., png, jpeg)
                $imageType = $matches[1][$index];

                // Check for valid image types (optional, for security)
                $allowedTypes = ['png', 'jpeg', 'jpg', 'gif', 'webp'];
                if (!in_array($imageType, $allowedTypes)) {
                    continue; // Skip if the image type is not allowed
                }

                // Get the base64 encoded image data
                $imageData = base64_decode($matches[2][$index]);

                // Generate a unique filename for the image
                $imageName = time() . '_' . uniqid() . '.' . $imageType;

                // Define the file path to store the image
                $directory = public_path('uploads/notice');
                $filePath = $directory . '/' . $imageName;

                // Ensure the directory exists
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true); // Create the directory if it doesn't exist
                }

                // Save the image to the server
                $saveResult = file_put_contents($filePath, $imageData);

                if ($saveResult !== false) {
                    // Replace the base64 image in the content with the new image URL
                    $imageUrl = asset('uploads/notice/' . $imageName);
                    $content = str_replace($matches[0][$index], '<img src="' . $imageUrl . '"', $content);
                } else {
                    // Optionally log the error or notify if saving the image fails
                    Log::error('Failed to save image: ' . $imageName);
                }
            }
        }

        return $content;
    }
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('notice_create')) {
            abort(403, 'Unauthorized');
        }

         //Validate the request data
        $validatedData = $request->validate([
            'title' =>[
                'required','max:255',
            ],
            'content' => 'required',
            'is_urgent' => 'required',
        ]);

        try {

            // Get the content with base64 images
            $content = $request['content'];

            // Convert base64 images to actual images and replace them in content
            $content = $this->handleImagesInContent($content);

            // Save the notice with the updated content (containing image URLs)
            $notice = Notice::create([
                'sister_concern_id' => auth()->user()->activeConcernId(),
                'title' => $request->title,
                'content' => $content,
                'is_urgent' => $request->is_urgent ?? 0,
            ]);

            $users = User::where('sister_concern_id',auth()->user()->activeConcernId())->get();
            foreach ($users as $user) {
                $user->notify(new NoticePosted($notice));
            }

            return redirect()->route('notices.index')->with('success', 'Notice created successfully');
        } catch (\Exception $e) {
            return redirect()->route('notices.create')
                ->withInput()
                ->with('error',$e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notice $notice)
    {
        if (!auth()->user()->hasPermissionTo('notice_edit')) {
            abort(403, 'Unauthorized');
        }
        return view('office_management.notice.edit', compact('notice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notice $notice)
    {
        if (!auth()->user()->hasPermissionTo('notice_edit')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'title' =>[
                'required','max:255',
            ],
            'content' => 'required',
            'is_urgent' => 'required',
        ]);

        try {

            // Get the content with base64 images
            $content = $request['content'];

            // Convert base64 images to actual images and replace them in content
            $content = $this->handleImagesInContent($content);

            // Update the notice with the new content (containing updated image URLs)
            $notice->update([
                'title' => $request->title,
                'content' => $content,
                'is_urgent' => $request->is_urgent ?? 0,
            ]);

            return redirect()->route('notices.index')->with('success', 'Notice updated successfully');
        } catch (\Exception $e) {

            return redirect()->route('notices.edit')->withInput()->with('error',$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notice $notice)
    {
        try {
            if (!auth()->user()->hasPermissionTo('notice_delete')) {
                abort(403, 'Unauthorized');
            }
            // Delete the Project record
            $notice->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Notice deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' =>$e->getMessage()], Response::HTTP_OK);

        }
    }
}
