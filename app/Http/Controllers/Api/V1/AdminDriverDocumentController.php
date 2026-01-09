<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDriverDocumentUpdateRequest;
use App\Models\DriverDocument;

class AdminDriverDocumentController extends Controller
{
    /**
     * @group Admin
     */
    public function index()
    {
        $documents = DriverDocument::query()
            ->with('driver.user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($documents);
    }

    /**
     * @group Admin
     */
    public function show(DriverDocument $driverDocument)
    {
        return response()->json(['driver_document' => $driverDocument->load('driver.user')]);
    }

    /**
     * @group Admin
     */
    public function update(AdminDriverDocumentUpdateRequest $request, DriverDocument $driverDocument)
    {
        $driverDocument->update($request->validated());

        return response()->json(['driver_document' => $driverDocument->fresh()]);
    }

    /**
     * @group Admin
     */
    public function destroy(DriverDocument $driverDocument)
    {
        $driverDocument->delete();

        return response()->json(['message' => 'Driver document deleted.']);
    }
}
