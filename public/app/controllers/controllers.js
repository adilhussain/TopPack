app.controller('SearchCtrlr', function($scope, $http) {

  $scope.startSearch = function() {
    if ($scope.keyword) {
      $scope.searching = true;
      $scope.searched_keyword = $scope.keyword;
      $scope.results = "";
      $http({
        method: 'GET',
        url: '/packages/javascript?keyword=' + $scope.keyword
      }).then(function successCallback(response) {
        $scope.searching = false;
        $scope.results = response.data;
      }, function errorCallback(response) {

      });
    }
  };

  $scope.importPackages = function(name, owner, description, html_url, item_no) {
    $("#button-" + item_no).addClass('d-none');
    $("#img-" + item_no).removeClass('d-none');
    $http({
      method: 'POST',
      url: '/package/import/',
      data: {
        url: html_url,
        owner: owner,
        description: description,
        name: name
      }
    }).then(function successCallback(response) {
      if (response.data.error) {
        $("#img-" + item_no).addClass('d-none');
        $("#fail-" + item_no).removeClass('d-none').text(response.data.message);
        $scope.dangerMessage = response.data.message;
      } else {
        $("#img-" + item_no).addClass('d-none');
        $("#success-" + item_no).removeClass('d-none').text("Imported Repo and " + response.data.packages.length + " packages.");
      }
    }, function errorCallback(response) {

    });
  };

});

app.controller('TopPackageCtrlr', function($scope, $http) {
  $http({
    method: 'GET',
    url: '/packages/top'
  }).then(function successCallback(response) {
    $scope.results = response.data;
  }, function errorCallback(response) {

  });
});
