<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * @group Activity Logs
 */
class ActivityLogController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $logs = Activity::query()
            ->with('causer:id,name,email', 'subject')
            ->when($request->causer_id, fn ($q, $id) => $q->where('causer_id', $id))
            ->when($request->log_name,   fn ($q, $n)  => $q->where('log_name', $n))
            ->when($request->event,      fn ($q, $e)  => $q->where('event', $e))
            ->when($request->date_from,  fn ($q, $d)  => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to,    fn ($q, $d)  => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->successResponse($logs);
    }

    public function show(Activity $log): JsonResponse
    {
        return $this->successResponse(
            $log->load('causer:id,name,email')
        );
    }
}
