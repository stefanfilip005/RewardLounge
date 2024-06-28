<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;

class CheckAccess
{
    /**
     * The attributes to check for access rights.
     *
     * @var array
     */
    protected $accessAttributes = [
        'is.admin' => ['isAdministrator', 'isDeveloper'], // Admins and Developers
        'is.developer' => ['isDeveloper'], // Only Developers
        'is.moderator' => ['isAdministrator', 'isModerator', 'isDeveloper'], // All
        'is.dienstfuehrer' => ['isAdministrator', 'isModerator', 'isDeveloper', 'isDienstfuehrer'], // DF
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if ($user && $user->remoteId) {
            $attributesToCheck = $this->accessAttributes[$role] ?? [];

            $employee = Employee::where('remoteId', $user->remoteId)
                                            ->where(function ($query) use ($attributesToCheck) {
                                                foreach ($attributesToCheck as $attribute) {
                                                    $query->orWhere($attribute, 1);
                                                }
                                            })
                                            ->first();

            if (!$employee) {
                // Return an unauthorized response or redirect
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return $next($request);
    }
}
