var app = angular.module('appSS',['ui.router',"ngResource"]) .factory('Annonces', ['$resource',
    function($resource){
      return $resource('/scriptsphp/monreste.php/job',
        {
			'query':  {method:'GET', isArray:true}
        /*  'update': {method: 'PUT'},
          'save': {method: 'POST'}*/
        }
      );
    }
  ]).factory('Requis', ['$resource',
    function($resource){
      return $resource('/scriptsphp/monreste.php/requis'
    /*  ,
        {
          'update': {method: 'PUT'},
          'save': {method: 'POST'}
        }*/
      );
    }
  ]);

 