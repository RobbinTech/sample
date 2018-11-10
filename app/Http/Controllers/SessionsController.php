<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    //引导到登陆页面
    public function create()
    {
        return view('sessions.create');
    }

    //用户登陆逻辑
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);
        /*
         * Auth::attempt(['email' => $email, 'password' => $password])
         * attempt 方法会接收一个数组来作为第一个参数，该参数提供的值将用于寻找数据库中的用户数据
         */
        if (Auth::attempt($credentials, $request->has('remember'))) {
            if (Auth::user()->activated) {
                session()->flash('success', '欢迎回来！');
            return redirect()->intended(route('users.show', [Auth::user()])); //intended 登该方法可将页面重定向到上一次请求尝试访问的页面上，并接收一个默认跳转地址参数，当上一次请求记录为空时，跳转到默认地址上。
            }else{
                Auth::logout();
                session()->flash('warning', '您的账号未激活，请检查邮箱中的注册邮件进行激活。(￣▽￣)"');
                return redirect('/');
            }
        }else{
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配！(￣▽￣)"');
            return redirect()->back()->withInput();
        }

        return;
    }

    //退出登录
    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出(￣▽￣)"');
        return redirect('login');
    }
}
