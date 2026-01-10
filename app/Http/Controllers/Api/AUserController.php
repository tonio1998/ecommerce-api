<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\User;
use App\Models\UserFCMToken;
use App\Services\NotificationService;
use App\Traits\TCommonFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AUserController extends Controller
{
    use TCommonFunctions;

    public function users(Request $request)
    {
        $search = $request->input('search');

        $users = User::select('id', 'name')
            ->where('user_type', 1)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    public function index(Request $request)
    {
        $users = User::with(['roles'])
            ->when($request->filled('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->paginate(15);

        return response()->json($users);
    }

    public function show($id)
    {
        $class = User::where('id', $id)->first();

        if (!$class) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($class);
    }

    public function update(Request $request, $id)
    {
        $class = User::where('id', $id)->first();

        if (!$class) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'location' => 'nullable',
            'Nationality' => 'required|string|max:255'
        ]);

        $validated['current_location'] = json_encode($validated['location'] ,true);
        $validated['name'] = $validated['fullName'];

        $class->update([
            ...$validated,
            'name' => $validated['name'],
            'updated_by' => Auth::id(),
        ]);
        return response()->json($class);
    }

    public function updateProfilePicture(Request $request, $id)
    {
        $validate = $request->validate([
            'image' => 'nullable|max:15360',
        ]);

        $user = User::findOrFail($id);

        if ($request->hasFile('image')) {
            $relativePath = $request->file('image')->store("user_files/{$id}/profile_pic", 'public');

            $source = storage_path('app/public/' . $relativePath);
            $destination = public_path('storage/' . $relativePath);
            $destinationDir = dirname($destination);

            if (!file_exists($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            copy($source, $destination);

            $user->profile_pic = $relativePath;
            $user->save();
        }

        return response()->json([
            'message' => 'Profile picture updated',
            'user' => $user,
            'profile_pic_url' => asset('storage/' . $user->profile_pic),
        ]);
    }
    public function saveFcmToken(Request $request)
    {
        $token = $request->input('token');
        $user = User::find(Auth::id());
        $existing = UserFCMToken::where('user_id', $user->id)->where('conn_id', $user->conn_id)->first();
        if (!$existing) {
            $userToken = new UserFCMToken();
            $userToken->user_type = $user->user_type;
            $userToken->user_id = $user->id;
            $userToken->conn_id = $user->conn_id;
            $userToken->fcm_token = $token;
            $this->setCommonFields($userToken);
            $userToken->save();

//            (new NotificationService)->notifyUserOrAll(
//                $classUserIds,
//                'Class Update 📚',
//                'Your instructor just posted new materials.'
//            );
        }
        return response()->json([
            'message' => 'FCM token saved successfully',
            'status' => 'success',
        ]);
    }

    public function updateRole(Request $request)
    {
        $user = User::find(Auth::id());
        $user->role = $request->role;
        $user->save();
        return response()->json(['message' => 'Role updated']);
    }

    public function saveLocation(Request $request)
    {
        $data = $request->except('user_id');

        $user = User::where('id', $request->user_id)->update([
            'current_location' => json_encode($data)
        ]);

        return response()->json([
            'message' => 'Location saved successfully',
            'user' => $user
        ]);
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($id);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 403);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }
}
