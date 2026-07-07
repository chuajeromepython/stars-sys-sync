<?php

namespace App\Http\Controllers;

use App\Models\CustomFunction;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppApiController extends Controller
{
    public function authenticate($username, $password)
    {

        $credentials = [
            'username' => $username,
            'password' => $password,
        ];
        $result = [
            'status' => 0,
            'data' => null,
            'message' => 'Not Found',
        ];
        if (Auth::attempt($credentials)) {
            $result['data'] = Auth::user();
            $result['status'] = '200';
            $result['message'] = 'Successfully login';
        }

        return response()->json($result);

    }

    public function syncClassroomsByTeacherUserId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => ['required', 'integer', 'exists:tbl_users,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        $user_id = (int) $request->input('userId');

        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated request. Please login first.',
                'data' => null,
            ], 401);
        }

        if ((int) Auth::id() !== $user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Submitted userId does not match the active session.',
                'data' => null,
            ], 403);
        }

        $user = User::find($user_id);

        if (! $user || $user->classification !== 'Teacher') {
            return response()->json([
                'success' => false,
                'message' => 'Only teacher accounts can sync classrooms.',
                'data' => null,
            ], 403);
        }

        $teacher = Teacher::where('user_id', $user_id)->first();

        if (! $teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found.',
                'data' => null,
            ], 404);
        }

        $classrooms = CustomFunction::getClassroomsByTeacherUserId($user_id);

        if (empty($classrooms)) {
            return response()->json([
                'success' => true,
                'message' => 'No classrooms found',
                'data' => (object) [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Classrooms synced successfully',
            'data' => $classrooms,
        ]);
    }
}
