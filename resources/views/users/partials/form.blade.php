<div id="profile-form-wrapper">
    <h3 class="all-caps">{{ __('Update profile') }}</h3>

    <form id="profile-form" action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('put')

        <div class="form-group row">
            <label for="inputName" class="col-sm-1-12 col-form-label"></label>
            <div class="col-sm-1-12">
                <input type="text" class="form-control" name="inputName" id="inputName" placeholder="">
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-sm-2 col-sm-10">
                <button type="submit" class="btn btn-dark btn-block">Action</button>
            </div>
        </div>
    </form>
</div>
