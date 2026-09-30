<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Question $question)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Question $question)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Question $question)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Question $question)
    {
        //
    }

    /**
     * Update answer key (question, options, and correct answer)
     *
     * @param  int  $id
     * @return Response
     */
    public function updateAnswerKey(Request $request, $id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'question' => 'required|string',
                'options' => 'required|array|min:4',
                'option_ids' => 'required|array|min:4',
                'correct_answer' => 'required|in:A,B,C,D',
            ]);

            DB::beginTransaction();

            // Update the question
            $question = Question::findOrFail($id);
            $question->question = $request->question;
            $question->save();

            // Update options and set correct answer
            $options = $request->options;
            $optionIds = $request->option_ids;
            $correctAnswer = $request->correct_answer;
            $assignments = ['A', 'B', 'C', 'D'];

            foreach ($assignments as $index => $assignment) {
                if (isset($optionIds[$index]) && $optionIds[$index]) {
                    $option = Option::findOrFail($optionIds[$index]);
                    $option->option = $options[$index];
                    $option->is_correct = ($assignment === $correctAnswer) ? 1 : 0;
                    $option->save();

                    \Log::info('Option updated', [
                        'option_id' => $option->id,
                        'assignment' => $assignment,
                        'is_correct' => $option->is_correct,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Answer key updated successfully!',
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation error: '.json_encode($e->errors()),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error updating answer key: '.$e->getMessage(),
            ], 500);
        }
    }
}
