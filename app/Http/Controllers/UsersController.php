<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;
class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            //except 除了此处指定的动作以外，所有其他动作都必须登录用户才能访问
            'except' => ['index', 'show', 'create', 'store']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
               'name' => $request->name,
               'email' => $request->email,
               'password' => bcrypt($request->password)
        ]);
        Auth::login($user); //设置注册成功自动登陆
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程(ง •_•)ง');
        return redirect()->route('users.show', $user->id);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);
        $data = [];
        $data['name'] = $request->name;
        if ($request->password){
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功(ง •_•)ง');
        return redirect()->route('users.show', $user->id);
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }
}
