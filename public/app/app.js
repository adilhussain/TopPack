var app = angular.module("topPackApp", ['ngRoute']);

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
    .otherwise({
      redirectTo: '/search'
    });
});
