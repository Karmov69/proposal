<?php

namespace App\Http\Controllers;

use App\Calculation;
use App\Stage;
use App\Template;
use App\TemplateData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CalculationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $calculations = Calculation::all();
        return view('calculations.index', compact('calculations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $templates = Template::get(['id', 'name']);
        return view('calculations.create', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Принимаем и сохраняем данные для создания нового расчёта
        if ($request->ajax()) {
//            Validator::make($request->all(), [
//                'id' => 'required',
//            ])->validate();
            Calculation::create([
                'name' => $request->name,
                'cost_per_hour' => $request->cost_per_hour,
                'user_name' => $request->user_name,
                'user_phone' => $request->user_phone,
                'user_email' => $request->user_email,
                'template_id' => $request->template_id,
                'additional_tasks' => json_encode($request->additional_tasks),
                'tasks' => json_encode($request->tasks),
                'info' => json_encode($request->info),
            ]);
            return response(['status' => 'Расчёт успешно создан'], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Calculation $calculation
     * @return \Illuminate\Http\Response
     */
    public function show(Calculation $calculation)
    {
//        $calculateData = TemplateData::where('id', $calculation->id)->get();
//        return view('calculations.show', compact('calculation'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Calculation $calculation
     * @return \Illuminate\Http\Response
     */
    public function edit(Calculation $calculation)
    {
        $calculateData = Calculation::where('id', $calculation->id)->get();
        return view('calculations.show', compact('calculateData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Calculation $calculation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Calculation $calculation)
    {
        if ($request->ajax()) {

//            $messages = [
//                'required' => 'Поле :attribute обязательно к заполнению.',
//                'numeric' => 'Поле :attribute должно быть числом',
//            ];
//
//            $this->validate($request, [
//                'name' => 'required|string|min:1',
//                'sort' => 'required|numeric',
//            ], $messages);

            $calculation->name = $request->name;
            $calculation->cost_per_hour = $request->cost_per_hour;
            $calculation->user_name = $request->user_name;
            $calculation->user_phone = $request->user_phone;
            $calculation->user_email = $request->user_email;
            $calculation->template_id = $request->template_id;
            $calculation->additional_tasks = json_encode($request->additional_tasks);
            $calculation->tasks = json_encode($request->tasks);
            $calculation->info = json_encode($request->info);
            $calculation->save();

        }
    }

    public function selectTemplate(Request $request)
    {
//        dd($request->all());
        //Принимаем айдишник шаблона и отдаём в ответе Этапы->задачи->варианты
        if ($request->ajax()) {
            Validator::make($request->all(), [
                'id' => 'required',
            ])->validate();

            // Получаем данные с расчетами по шаблону
            $calculateData = TemplateData::with('variant', 'task')->where('template_id', $request->id)->get();

            // Получаем спсок всех этапов
            $stages = Stage::with('tasks')->get();

            // Формируем пустой массив, в который будем складывать данные в нужной структуре
            $result = [];

            // Перебираем все этапы, так как нужна красивая группировка по этапам и данные по эатапм
            foreach ($stages as $stage) {
                // фильтруем задачи этапа
                $taskForStage = $calculateData->whereIn('task_id', $stage->tasks->pluck('id'))->groupBy('task_id');
                $resultTask = [];
                foreach ($taskForStage as $task) {
                    $resultVariant = [];
                    foreach ($task as $data) {
                        $variant = $data->variant;
                        $resultVariant[] = [
                            'id' => $variant->id,
                            'name' => $variant->name,
                            'variant_time' => $data->variant_time
                        ];
                    }
                    if (count($resultVariant) > 0) {
                        $resultTask[] = [
                            'id' => $task->first()->task->id,
                            'name' => $task->first()->task->name,
                            'variants' => $resultVariant,
                        ];

                    }
                }
                if (count($resultTask) > 0) {
                    $result[] = [
                        'name' => $stage->name,
                        'id' => $stage->id,
                        'tasks' => $resultTask
                    ];
                }
            }


            return response($result, 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\calculation $calculation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Calculation $calculation)
    {
        $calculation->delete();
        return redirect(route('calculations.index'))->with('status', 'Расчёт удалён');
    }
}
