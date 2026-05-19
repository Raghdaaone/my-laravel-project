<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{

public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|min:6|confirmed', // يشترط وجود حقل password_confirmation
        ], [
            'name.required'      => 'الاسم مطلوب',
            'email.required'     => 'البريد الإلكتروني مطلوب',
            'email.unique'       => 'هذا البريد مستخدم مسبقاً',
            'password.required'  => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'كلمات المرور غير متطابقة',
        ]);

        // إنشاء المستخدم في جدول users كـ مريض
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => 'patient',
            'is_active' => true,
        ]);

        // توليد توكن مباشر للمريض ليدخل فوراً بعد التسجيل
        $token = $user->createToken('patient-token')->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء حساب المريض بنجاح ✅',
            'token'   => $token,
            'user'    => $user
        ], 201);
    }

    /**
     * 2️⃣ وظيفة تسجيل الدخول التقليدية للمريض (Login)
     * تستقبل: الإيميل والباسورد
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'البريد الإلكتروني مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        // البحث عن المستخدم بشرط أن يكون دوره مريض
        $user = User::whereEmail($request->email)
                    ->where('role', 'patient')
                    ->first();

        if (!$user || !Hash::make($request->password, $user->password)) {
            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة، أو الحساب ليس مريضاً'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'هذا الحساب تم إيقافه مؤقتاً، يرجى مراجعة الإدارة'
            ], 403);
        }

        // إنشاء التوكن
        $token = $user->createToken('patient-token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح للمريض  ✅',
            'token'   => $token,
            'user'    => $user,
            'role'    => $user->role
        ], 200);
    }

    /**
     * 3️⃣ وظيفة تسجيل الخروج للمريض (Logout)
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            // حذف التوكن الحالي المستخدم في هذا الطلب لضمان خروج آمن من الجهاز الحالي
        $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();
        }

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح للمريض 🚪👋'
        ], 200);
    }

    // ─── إكمال البيانات الصحية + إنشاء التوكن ──────────────────────
    // تُستدعى بعد التحقق من Google
    public function completeProfile(Request $request): JsonResponse
{
    $request->validate([
        'email'     => 'required|email|exists:users,email',
        'gender'    => 'required|in:male,female',
        'weight'    => 'required|numeric|min:1|max:500',
        'age'       => 'required|integer|min:1|max:120',
        'latitude'  => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
    ]);

    $user = User::whereEmail($request->email)
                ->where('role', 'patient')
                ->first();

    if (!$user) {
        return response()->json([
            'message' => 'المستخدم غير موجود كـ مريض',
        ], 404);
    }

    // ❌ تم إزالة شرط (google_verified) لأن التسجيل أصبح تقليدياً بالكامل

    // تحقق أن الملف الطبي لم ينشأ مسبقاً
    if (Patient::where('user_id', $user->id)->exists()) {
        return response()->json([
            'message' => 'البيانات الطبية موجودة مسبقاً',
        ], 422);
    }

    // إنشاء السجل الطبي للمريض وربطه بالحساب
    Patient::create([
        'user_id'   => $user->id,
        'gender'    => $request->gender,
        'weight'    => $request->weight,
        'age'       => $request->age,
        'latitude'  => $request->latitude  ?? null,
        'longitude' => $request->longitude ?? null,
    ]);

    return response()->json([
        'message' => 'تم حفظ البيانات الطبية وإكمال الملف بنجاح ✅',
        'user'    => $user
    ], 200);
}
}
