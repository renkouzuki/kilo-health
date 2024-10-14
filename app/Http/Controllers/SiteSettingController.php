<?php

namespace App\Http\Controllers;

use App\Repositories\SiteSettings\SiteSettingInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(SiteSettingInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

    public function index(): JsonResponse
    {
        try {
            $settings = $this->Repository->getAllSettings();
            return response()->json(['success' => true, 'message' => 'Successfully retrieving settings', 'data' => $settings], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(string $key): JsonResponse
    {
        try {
            $setting = $this->Repository->getSetting($key);
            if (!$setting) {
                return response()->json(['message' => 'Setting not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully retrieving setting', 'data' => $setting], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(string $key): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'value' => 'required|string',
            ]);

            $updated = $this->Repository->updateSetting($key, $validatedData['value']);
            if (!$updated) {
                return response()->json(['message' => 'Setting not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Setting updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'key' => 'required|string|unique:site_settings,key',
                'name' => 'required|string',
                'value' => 'required|string',
                'input_type' => 'required|in:text,number,date,boolean,image',
            ]);

            $setting = $this->Repository->createSetting($validatedData);
            return response()->json(['success' => true, 'message' => 'Successfully created setting', 'data' => $setting], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $key): JsonResponse
    {
        try {
            $deleted = $this->Repository->deleteSetting($key);
            if (!$deleted) {
                return response()->json(['message' => 'Setting not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Failed to delete setting'], 204);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
