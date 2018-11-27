var app = angular.module("topPackApp", ['ngRoute', 'ui.bootstrap']);

app.config(function($routeProvider) {
  $routeProvider
    .when('/search', {
      controller: 'SearchCtrlr',
      templateUrl: '/app/partials/search.html'
    })
    .when('/packages', {
      controller: 'TopPackageCtrlr',
      templateUrl: '/app/partials/top.html'
    })
    .when('/all_packages', {
      controller: 'TopPackageCtrlrAll',
      templateUrl: '/app/partials/top.html'
    })
    .when('/package', {
      controller: 'PackageCtrlr',
      templateUrl: '/app.partials/top.html'
    })
    .otherwise({
      redirectTo: '/search'
    });
});
