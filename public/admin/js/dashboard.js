var adminDashboard = function () {
    var init = function (settingUrl, chartUrl) {
        $('#note').blur(function() {
            const formData = new FormData();
            formData.append('key','site.note');
            formData.append('site.note',$(this).val()); 

            $.ajax({ 
                type: 'POST', 
                url: settingUrl, 
                data: formData, 
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                }
            });
        });

        $.ajax({ 
            type: 'GET', 
            url: chartUrl, 
            dataType: 'json',
            success: function (response) {
                Chart.defaults.plugins.legend.display = false;
                Chart.defaults.scale.beginAtZero = true
                var options = {
                    maintainAspectRatio: false,
                    scale: {
                        ticks: {
                          precision: 0
                        }
                      }
                }
                new Chart("userChart", {
                    type: "line",
                    data: {
                        labels: response.user.label,
                        datasets: [{
                            data: response.user.data,
                        }]
                    },
                    options : options
                });
                new Chart("pageChart", {
                    type: "line",
                    data: {
                        labels: response.page.label,
                        datasets: [{
                            data: response.page.data,
                        }]
                    },
                    options : options
                });
                new Chart("postChart", {
                    type: "line",
                    data: {
                        labels: response.post.label,
                        datasets: [{
                            data: response.post.data,
                        }]
                    },
                    options : options
                });
                new Chart("reportChart", {
                    type: "line",
                    data: {
                        labels: response.report.label,
                        datasets: [{
                            data: response.report.data,
                        }]
                    },
                    options : options
                });

                new Chart("groupChart", {
                    type: "line",
                    data: {
                        labels: response.group.label,
                        datasets: [{
                            data: response.group.data,
                        }]
                    },
                    options : options
                });
            }
        });
    }
  
    return {
        init: init
    };
}();
