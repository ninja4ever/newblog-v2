<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\PostCategory;

class Post extends Model
{

  protected $guarded = array();

  /**
   * Get the user that owns the posts
   */
  public function user()
  {
      return $this->belongsTo(User::class);
  }
  /**
   * [scopeOrder description]
   * @param  [type] $query [description]
   * @return [type]        [description]
   */
  public function scopeOrder($query)
  {
    return $query->orderBy('created_at', 'desc');
  }
  /**
   * [scopeOnlyActive description]
   * @param  [type] $query [description]
   * @return [type]        [description]
   */
  public function scopeOnlyActive($query)
  {
    return $query->where('active', 1);
  }
  /**
   * [postcategory description]
   * @return [type] [description]
   */
  public function postcategory()
  {
    return $this->belongsTo(PostCategory::class, 'category');
  }
}
