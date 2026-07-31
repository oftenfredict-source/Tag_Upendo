@extends('layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-paper-plane"></i> Send Bulk SMS</h1>
            <p>Message multiple members at once</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="tile">
                <h3 class="tile-title">Compose Bulk Message</h3>
                <div class="tile-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form action="{{ route('bulk-sms.store') }}" method="POST"
                        data-swal-confirm="{{ __('Are you sure you want to send this bulk SMS?') }}">
                        @csrf

                        <div class="form-group">
            <label class="control-label">Target Audience</label>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="radio" name="target" value="all" checked onchange="toggleTarget('all')">
                All Members
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="radio" name="target" value="department" onchange="toggleTarget('department')">
                Specific Department
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="radio" name="target" value="member" onchange="toggleTarget('member')">
                Specific Member(s)
              </label>
            </div>
          </div>

          <div class="form-group" id="department-select" style="display: none;">
            <label class="control-label">Select Department</label>
            <select class="form-control" name="department_id">
              <option value="">-- Choose a Department --</option>
              @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group" id="member-select" style="display: none;">
            <label class="control-label">Select Member(s)</label>
            <select class="form-control" name="member_ids[]" multiple style="height: 150px;">
              @foreach($members as $m)
                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->phone_number }})</option>
              @endforeach
            </select>
            <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple members.</small>
          </div>
                        <div class="form-group">
                            <label class="control-label">Message</label>
                            <textarea class="form-control" name="message" rows="5" required
                                placeholder="Type the broadcast SMS content here..."></textarea>
                        </div>

                        <div class="form-group mt-4">
                            <button class="btn btn-primary" type="submit"><i
                                    class="fa fa-fw fa-lg fa-paper-plane"></i>Send Broadcast Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
  function toggleTarget(target) {
    var deptElem = document.getElementById('department-select');
    var memberElem = document.getElementById('member-select');
    
    if(target === 'department') {
      deptElem.style.display = 'block';
      memberElem.style.display = 'none';
    } else if(target === 'member') {
      deptElem.style.display = 'none';
      memberElem.style.display = 'block';
    } else {
      deptElem.style.display = 'none';
      memberElem.style.display = 'none';
    }
  }
</script>
    @endpush
@endsection