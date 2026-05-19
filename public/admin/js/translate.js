var adminTranslate = function () {
  var dataTranslate = {};
  var add = function (data) {
    $.extend(dataTranslate, data);
  }
  var __ = function(key){
    if (key in dataTranslate){
      return dataTranslate[key];
    }
    return '';
  }
  return {
    add: add,
    __: __
  };
}();