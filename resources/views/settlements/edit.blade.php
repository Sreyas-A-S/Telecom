@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Settlement for {{ $settlement->employee_name }}</h4>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('settlements.update',$settlement) }}"method="POST">@csrf@method('PUT')<divclass="row"><divclass="col-md-4mb-3"><labelfor="employee_code"class="form-label">EmployeeCode</label><inputtype="text"class="form-control"id="employee_code"name="employee_code"value="{{ old('employee_code',$settlement->employee_code) }}"required></div><divclass="col-md-4mb-3"><labelfor="employee_name"class="form-label">NameofEmployee</label><inputtype="text"class="form-control"id="employee_name"name="employee_name"value="{{ old('employee_name',$settlement->employee_name) }}"required></div><divclass="col-md-4mb-3"><labelfor="age"class="form-label">Age</label><inputtype="number"class="form-control"id="age"name="age"value="{{ old('age',$settlement->age) }}"></div></div><divclass="row"><divclass="col-md-4mb-3"><labelfor="department"class="form-label">Department</label><inputtype="text"class="form-control"id="department"name="department"value="{{ old('department',$settlement->department) }}"></div><divclass="col-md-4mb-3"><labelfor="head_office_branch"class="form-label">HeadOffice/Branch</label><inputtype="text"class="form-control"id="head_office_branch"name="head_office_branch"value="{{ old('head_office_branch',$settlement->head_office_branch) }}"></div><divclass="col-md-4mb-3"><labelfor="designation"class="form-label">Designation</label><inputtype="text"class="form-control"id="designation"name="designation"value="{{ old('designation',$settlement->designation) }}"></div></div><divclass="row"><divclass="col-md-4mb-3"><labelfor="date_of_joining"class="form-label">DateofJoining</label><inputtype="date"class="form-control"id="date_of_joining"name="date_of_joining"value="{{ old('date_of_joining',$settlement->date_of_joining) }}"required></div><divclass="col-md-4mb-3"><labelfor="date_of_resignation"class="form-label">DateofResignation</label><inputtype="date"class="form-control"id="date_of_resignation"name="date_of_resignation"value="{{ old('date_of_resignation',$settlement->date_of_resignation) }}"></div><divclass="col-md-4mb-3"><labelfor="reason_for_resignation"class="form-label">ReasonforResignation</label><textareaclass="form-control"id="reason_for_resignation"name="reason_for_resignation"rows="3">{{ old('reason_for_resignation',$settlement->reason_for_resignation) }}</textarea></div></div><buttontype="submit"class="btnbtn-primary">UpdateSettlement</button><ahref="{{ route('settlements.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
