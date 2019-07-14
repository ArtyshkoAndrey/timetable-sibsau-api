<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\LessonRequest as StoreRequest;
use App\Http\Requests\LessonRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use App\Http\Controllers\ParseController;
use App\Lesson;
use App\Group;
/**
 * Class LessonCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class LessonsCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Lesson');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/lessons');
        $this->crud->setEntityNameStrings('lesson', 'lessons');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */
        $this->crud->disableDetailsRow();
        $this->crud->enableResponsiveTable();
        $this->crud->denyAccess(['create', 'delete', 'update']);
        $this->crud->addButtonFromView('top', 'Обновить расписание групп', 'moderate', 'beginning');
        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->addColumn(['name' => 'name', 'type' => 'text', 'label' => 'Name']);
        $this->crud->addColumn(['name' => 'group_id', 'label' => 'Group', 'type' => 'select', 'entity' => 'group', 'attribute' => "name", 'model' => "App\Group", ]);
        $this->crud->addColumn(['name' => 'teacher_id', 'label' => 'Teacher', 'type' => 'select', 'entity' => 'teacher', 'attribute' => "initials_name", 'model' => "App\Teacher", ]);
        $this->crud->addColumn(['name' => 'type', 'type' => 'text', 'label' => 'Type']);
        $this->crud->setFromDb();

        // add asterisk for fields that are required in LessonRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }
    public function moderate () {
      $lessons = Lesson::get();
      if (count($lessons) > 0) {
        $lessons->each->delete();
      }
      $parser = new ParseController();
      $groups = Group::all();
      foreach ($groups as $group) {
        $timetable = json_decode(json_encode($parser->group($group->id)))->original;
        $countDay = 1;
        $countWeek = 1;
        if ($timetable->timetable)
        foreach ($timetable->timetable as $week) {
          foreach ($week as $day) {
            foreach ($day->lessons as $lesson) {
              if(!is_array($lesson)) {
                $teacher_id = explode('/', $lesson->teacherlink);
                $teacher_id = $teacher_id[count($teacher_id) - 1];
                $dbLesson = new Lesson();
                $dbLesson->name = $lesson->name;
                $dbLesson->group_id = $group->id;
                $dbLesson->teacher_id = $teacher_id;
                $dbLesson->audience = $lesson->audience;
                $dbLesson->time = [
                  'end' => $lesson->time[1],
                  'start' => $lesson->time[0]
                ];
                $dbLesson->type = $lesson->type;
                $dbLesson->week = $countWeek;

                $dbLesson->day = [
                  'name' => $day->nameDay,
                  'index' => $day->index
                ];
                if (isset($lesson->subGroup)) {
                  $dbLesson->subgroup = $lesson->subGroup;
                }
                $dbLesson->prefLesson_id = 0;
                $dbLesson->lesson_image_id = 1;
                $dbLesson->save();
              } else {
                for ($i = 0; $i < count($lesson); $i++) {
                  $teacher_id = explode('/', $lesson[$i]->teacherlink);
                  $teacher_id = $teacher_id[count($teacher_id) - 1];
                  $dbLesson = new Lesson();
                  $dbLesson->name = $lesson[$i]->name;
                  $dbLesson->group_id = $group->id;
                  $dbLesson->teacher_id = $teacher_id;
                  $dbLesson->audience = $lesson[$i]->audience;
                  $dbLesson->time = [
                    'end' => $lesson[$i]->time[1],
                    'start' => $lesson[$i]->time[0]
                  ];
                  $dbLesson->type = $lesson[$i]->type;
                  $dbLesson->week = $countWeek;

                  $dbLesson->day = [
                    'name' => $day->nameDay,
                    'index' => $day->index
                  ];
                  if (isset($lesson[$i]->subGroup)) {
                    $dbLesson->subgroup = $lesson[$i]->subGroup;
                  }
                  if ($i > 0) {
                    $dbLesson->prefLesson_id = $prefId;
                  } else {
                    $dbLesson->prefLesson_id = 0;
                  }
                  $dbLesson->lesson_image_id = 1;
                  $dbLesson->save();
                  if ($i !== count($lesson) - 1) {
                    $prefId = $dbLesson->id;
                  }
                }
              }
            }
            $countDay++;
          }
          $countWeek++;
        }
      }
      return redirect(backpack_url('lessons'));
    }
    public function store(StoreRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }

    public function update(UpdateRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::updateCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }
}
