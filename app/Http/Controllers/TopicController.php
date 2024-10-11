<?php

namespace App\Http\Controllers;

use App\Http\Resources\TopicResource;
use App\Repositories\Topics\TopicInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TopicController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(TopicInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

    public function index(): JsonResponse
    {
        try {
            $topics = $this->Repository->getAllTopics();
            return response()->json(['success' => true, 'message' => 'successfully retrieving topics data', 'data' => TopicResource::collection($topics)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving topics', 'err' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'required|max:255',
                'category_id' => 'required|exists:categories,id',
            ]);

            $topic = $this->Repository->createTopic($validatedData);
            return response()->json(['success' => true, 'message' => 'Successfully store topic data', 'data' => new TopicResource($topic)], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error creating topic', 'err' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $topic = $this->Repository->getTopicById($id);
            if (!$topic) {
                return response()->json(['message' => 'Topic not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully retrieved topic data', 'data' => new TopicResource($topic)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving topic', 'err' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'required|max:255',
                'category_id' => 'required|exists:categories,id',
            ]);

            $updated = $this->Repository->updateTopic($id, $validatedData);
            if (!$updated) {
                return response()->json(['message' => 'Topic not found'], 404);
            }
            $topic = $this->Repository->getTopicById($id);
            return response()->json(['success' => true, 'message' => 'Successfully updated topic data', 'data' => new TopicResource($topic)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating topic', 'err' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->Repository->deleteTopic($id);
            if (!$deleted) {
                return response()->json(['message' => 'Topic not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'successfully deleted topic data'], 204);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting topic', 'err' => $e->getMessage()], 500);
        }
    }

    public function getByCategory(int $categoryId): AnonymousResourceCollection|JsonResponse
    {
        try {
            $topics = $this->Repository->getTopicsByCategory($categoryId);
            return response()->json(['success' => true , 'message' => 'Successfully retrieving topics' , 'data'=>TopicResource::collection($topics)],200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving topics', 'err' => $e->getMessage()], 500);
        }
    }
}
