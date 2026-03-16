<h2>Add User</h2>

<form action="/users" method="POST">
@csrf

<input type="text" name="name" placeholder="Name">

<input type="email" name="email" placeholder="Email">

<input type="password" name="password" placeholder="Password">

<button type="submit">Save</button>

</form>