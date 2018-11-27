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

  $scope.importPackages = function(name, owner, description, html_url, item_no, stargazers_count, watchers_count, forks_count) {
    debugger;
    $("#button-" + item_no).addClass('d-none');
    $("#button_1-" + item_no).addClass('d-none');
    $("#img-" + item_no).removeClass('d-none');
    $http({
      method: 'POST',
      url: '/package/import/',
      data: {
        url: html_url,
        owner: owner,
        description: description || "",
        name: name,
        stargazers_count: stargazers_count,
        watchers_count: watchers_count,
        forks_count: forks_count
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
    debugger;
    $scope.results = response.data;
    $scope.names = Object.keys(response.data);
    $scope.filter_name = "";
  }, function errorCallback(response) {

  });
});


app.controller('TopPackageCtrlrAll', function($scope, $http) {
  $http({
    method: 'GET',
    url: '/packages/all'
  }).then(function successCallback(response) {
    debugger;
    $scope.results = response.data;
    $scope.names = Object.keys(response.data);
    $scope.filter_name = "";

    $scope.viewby = 10;
      $scope.totalItems = $scope.names.length;
      $scope.currentPage = 4;
      $scope.itemsPerPage = $scope.viewby;
      $scope.maxSize = 5; //Number of pager buttons to show

      $scope.setPage = function (pageNo) {
        $scope.currentPage = pageNo;
      };

      $scope.pageChanged = function() {
        console.log('Page changed to: ' + $scope.currentPage);
      };

    $scope.setItemsPerPage = function(num) {
      $scope.itemsPerPage = num;
      $scope.currentPage = 1; //reset to first page
    }

  }, function errorCallback(response) {

  });
});


app.controller('PackageCtrlr', function($scope, $http) {
  $http({
    method: 'GET',
    url: '/packages/all'
  }).then(function successCallback(response) {
    debugger;
    $scope.results = response.data;
    $scope.names = Object.keys(response.data);
    $scope.filter_name = "";
  }, function errorCallback(response) {

  });
});
