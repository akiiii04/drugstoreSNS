<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Tag;
use App\Models\Post_Tag;
use App\Http\Requests\PostRequest;
use Auth;
use Cloudinary;

class PostController extends Controller
{
    public function index(Post $post, $type, Request $request)
    {
        $search = $request->input('search');
        $spaceConversion = mb_convert_kana($search, 's');
        $keywordArray = explode(' ', $spaceConversion);
        
        $unsolved = $request->input('unsolved');
        if($unsolved)$unsolvedValues = [1];
        else $unsolvedValues = [0, 1];
        
        $query = Post::query();
        
        $query->where('post_id', NULL)->where('type_id', $type)->whereIn('unsolved', $unsolvedValues);
        
        foreach ($keywordArray as $keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('body', 'like', '%' . $keyword . '%')
                  ->orWhereHas('tags', function ($subquery) use ($keyword) {
                      $subquery->where('name', 'like', '%' . $keyword . '%');
                  });
            });
        }
        $posts = $query->orderBy('updated_at', 'DESC')->paginate(10);
        
             return view('posts.index')->with(['posts' => $posts])
             ->with(['search' => $search])
             ->with(['type' => $type])
             ->with(['unsolved' => $unsolved]);
    }
    
    public function create($type)
    {
        return view('posts.create')->with(['type' => $type]);
    }
    
    public function store(PostRequest $request, Post $post)
    {
        $input_post = $request['post'];
         if($request->file('picture')){
            $image_url = Cloudinary::upload($request->file('picture')->getRealPath())->getSecurePath();
            $input_post += ['picture' => $image_url];
         }
        
        $post->fill($input_post)->save();
        
        if($post->post_id && $post->anonymity == 1){
            $post->anonymity = $post->set_anonymity();
            $post->save();
        }
        if($post->post_id)return redirect('/posts/' . $post->parentpost->id)->with('flash_message', '投稿を完了しました');
        
        preg_match_all('/#([a-zA-Z0-9０-９ぁ-んァ-ヶー一-龠]+)/u', $request->tag_name, $match); //#表記されたタグを配列に格納
        foreach($match[1] as $input_tag)
        {
	        $tag=Tag::firstOrCreate(['name'=>$input_tag]);//tagテーブルにないときは新しくtagを作成
	        $tag=null;
            $tag_id=Tag::where('name',$input_tag)->get(['id']);
            $post->tags()->attach($tag_id);
        }
        return redirect('/posts/' . $post->id)->with('flash_message', '投稿を完了しました');
    }
    
    public function show(Post $post)
    {
        if($post->post_id==NULL) //通常の投稿として処理
        return view('posts.show')->with(['post' => $post]);
        
        else{ //返信として処理
            $parentPost = Post::find($post->post_id);
            return view('posts.show_reply')->with(['post' => $post]);
        }

    }
    
    public function edit(Post $post)
    {
        if($post->user_id==Auth::id()){//ログインユーザーと投稿者が一致するとき編集可能
            if($post->post_id==NULL){ //通常の投稿として処理
                $tag_input="";
                $tags=Post_Tag::where("post_id", $post->id)->get();
                foreach($tags as $tag)
                {
                    $tag_name=Tag::where("id", $tag->tag_id)->first();
                    $tag_input.="#".$tag_name->name;
                }
                return view('posts.edit')->with(['post' => $post])->with(['tag' => $tag_input]);
            }
            else{ //返信として処理
                $parentPost = Post::find($post->post_id);
                return view('posts.edit_reply')->with(['post' => $post])->with(['parent' => $parentPost]);
            }
        }

    }
    public function edit_picture(Post $post)
    {   
        if($post->user_id==Auth::id())//ログインユーザーと投稿者が一致するとき編集可能
        return view('posts.edit_picture')->with(['post' => $post]);
    }
     public function update_picture(PostRequest $request, Post $post)
    {
        $input_post = $request['post'];
        if($request->file('picture')){
            $image_url = Cloudinary::upload($request->file('picture')->getRealPath())->getSecurePath();
            $input_post += ['picture' => $image_url];
        }
        else $input_post += ['picture' => NULL];
        $post->fill($input_post)->save();
        return redirect('/posts/' . $post->id)->with('flash_message', '画像を変更しました');
    }
    
    public function update(PostRequest $request, Post $post)
    {
        if($post->user_id==Auth::id()){
            if($post->post_id==NULL){
                $input_post = $request['post'];
                if($input_post['anonymity'] == 1 && $post->anonymity == 0)
                    $input_post['anonymity'] = $post->set_anonymity();
                else if($input_post['anonymity'] == 1 && $post->anonymity > 0)
                    $input_post['anonymity'] = $post->anonymity;
                $post->fill($input_post)->save();
                $tag_old=Post_Tag::where('post_id', $post->id)->delete(); //元々あったタグを削除
                preg_match_all('/#([a-zA-Z0-9０-９ぁ-んァ-ヶー一-龠]+)/u', $request->tag_name, $match); //#表記されたタグを配列に格納
                foreach($match[1] as $input_tag)
                {
        	        $tag=Tag::firstOrCreate(['name'=>$input_tag]);//tagテーブルにないときは新しくtagを作成
        	        $tag=null;
                    $tag_id=Tag::where('name',$input_tag)->get(['id']);
                    $post->tags()->attach($tag_id);
                }
                return redirect('/posts/' . $post->id)->with('flash_message', '投稿を編集しました');
            }
            else{
                $input_post = $request['post'];
                if($input_post['anonymity'] == 1 && $post->anonymity == 0)
                    $input_post['anonymity'] = $post->set_anonymity();
                else if($input_post['anonymity'] == 1 && $post->anonymity > 0)
                    $input_post['anonymity'] = $post->anonymity;
                $post->fill($input_post)->save();
                return redirect('/posts/' . $post->parentpost->id)->with('flash_message', '投稿を編集しました');
            }
        }
    }
    
    public function delete(Post $post){
        $post->delete();
        return redirect($post->type_id . '/index')->with('flash_message', '投稿を削除しました');
    }
    
    public function reply(Post $post)
    {
        return view('posts.create_reply')->with(['post' => $post]);
    }
    public function show_profile(User $user, $type)
    {
        $posts = Post::where('user_id', $user->id)->where('post_id', NULL)->where('type_id', $type)
            ->where('anonymity', 0)->orderBy('updated_at', 'DESC')->paginate(10);
        return view('profile.show')->with(['user' => $user])->with(['posts' => $posts])->with(['type' => $type]);
    }

}
