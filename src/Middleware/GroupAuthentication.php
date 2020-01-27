<?php

namespace Netflex\Pages\Middleware;

use Closure;

use Netflex\Pages\Page;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class GroupAuthentication extends Middleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @param  string[]  ...$guards
   * @return mixed
   *
   * @throws \Illuminate\Auth\AuthenticationException
   */
  public function handle($request, Closure $next, ...$guards)
  {
    if ($page = Page::current()) {
      if ($page->public) {
        return $next($request);
      }

      $this->authenticate($request, $guards);

      if (!$request->user()) {
        $this->unauthenticated($request, $guards);
      }

      $authGroups = collect($page->authgroups);
      $userGroups = collect($request->user()->groups);

      $hasPermission = !!($authGroups->first(function ($authgroup) use ($userGroups) {
        return $userGroups->contains($authgroup);
      }));

      if (!$hasPermission) {
        $this->unauthenticated($request, $guards);
      }
    }

    return $next($request);
  }
}
