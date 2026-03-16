<h2>User List</h2>

<a href="/users/create">Add User</a>

@foreach($users as $user)

<p>
{{ $user->name }} | {{ $user->email }}

<a href="/users/{{$user->id}}/edit">Edit</a>

<form action="/users/{{$user->id}}" method="POST">
@csrf
@method('DELETE')

<button>Delete</button>
</form>

</p>

@endforeach