$(function() {

	$('.btn-domain').click(function(){
		
		$('.card-result').fadeOut();
   		$('#dt_raw').dataTable().fnDestroy();
        var domain_id = $(this).data('domain');
        $('#domain_header').removeClass();
        $('#domain_header').addClass('text-center card-header text-bold bg-'+colors[domain_id-1]);
        $('#domain_header').html(domains[domain_id-1]['domain']);
        console.log(results);
        var html = "";
        var total_competencies = 0;
        var key_period = 0;
        total_competencies = (results['bosy']['has_data'] == 1) ? results['bosy']['domains'][domain_id]['competencies'].length : 0;
        total_competencies = (results['eosy']['has_data'] == 1) ? results['eosy']['domains'][domain_id]['competencies'].length : 0;
        key_period = (results['bosy']['has_data'] == 1) ? 'bosy' : 0;
        key_period = (results['eosy']['has_data'] == 1) ? 'bosy' : 0;
        for (var i = 0; i < total_competencies; i++) {
        	html += "<tr>"
        	if (results['bosy']['has_data'] == 1) {
        		var bp = (results['bosy']['domains'][domain_id]['competencies'][i]['p'] == 1) ? 1:0;
        		var bo = (results['bosy']['domains'][domain_id]['competencies'][i]['o'] == 1) ? 1:0;
        		var br = (results['bosy']['domains'][domain_id]['competencies'][i]['r'] == 1) ? 1:0;

        		html+="<td style='height: 60px;' class='p-1'>"+results['bosy']['domains'][domain_id]['competencies'][i]['competency']+"</td>"
        		html+="<td style='height: 60px;' class='text-center p-1'>"+bp+"</td>"
        		html+="<td style='height: 60px;' class='text-center p-1'>"+bo+"</td>"
        		html+="<td style='height: 60px;' class='text-center p-1'>"+br+"</td>"
        	}
        	if (results['eosy']['has_data'] == 1) {
				var ep = (results['eosy']['domains'][domain_id]['competencies'][i]['p'] == 1) ? 1:0;
				var eo = (results['eosy']['domains'][domain_id]['competencies'][i]['o'] == 1) ? 1:0;
				var er = (results['eosy']['domains'][domain_id]['competencies'][i]['r'] == 1) ? 1:0;
				html+="<td style='height: 60px;' class='text-center p-1'>"+ep+"</td>"
				html+="<td style='height: 60px;' class='text-center p-1'>"+eo+"</td>"
				html+="<td style='height: 60px;' class='text-center p-1'>"+er+"</td>"
        	}
        	html+="</tr>"
        }

        $('#tbody_raw').html(html);

        $('#dt_raw').dataTable({
		    'language':{
		        'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
		    },
		    'pageLength': 5,
		    "lengthChange": false,
		});

		$('#card_body_2').height($('#card_body_1').height());
		$('#card_body_2').css('overflow', 'auto');
		$('.card-result').fadeIn();
    });
})