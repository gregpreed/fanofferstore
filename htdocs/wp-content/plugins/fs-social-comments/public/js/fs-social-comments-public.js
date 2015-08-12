(function( $ ) {
	'use strict';

	window.fbAsyncInit = function () {
		FB.Event.subscribe('comment.create', function(post) {
			 $.ajax({
			        url: ajaxurl,
			        data: {
			            'action':'fs_social_comments_get_facebook_comment',
			            'comment_id': post.commentID,
			        },
			        success:function(data) {
			        	console.log(data);
			        	$.ajax({
					        url: ajaxurl,
					        data: {
					            'action':'fs_social_comments_register_comment',
					            'name':   data.from.name,
					            'comment_id': data.id,
		    			        'comment_txt': data.message,
		    			        'comment_post_id': jQuery("#commentPostId").val()
					        },
					        success:function(data) {
					            console.log(data);
					        },
					        error: function(errorThrown){
					            console.log(errorThrown);
					        }
					    });  
			        },
			        error: function(errorThrown){
			            console.log(errorThrown);
			        }
			});  
			 /*
			FB.api(
				    "/"+post.commentID,
				    {access_token: accessToken},
				    function (response) {
				      if (response && !response.error) {
				    	  $.ajax({
						        url: ajaxurl,
						        data: {
						            'action':'fs_social_comments_register_comment',
						            'name':   response.from.name,
						            'comment_id': response.id,
			    			        'comment_txt': response.message,
			    			        'comment_post_id': jQuery("#commentPostId").val()
						        },
						        success:function(data) {
						            console.log(data);
						        },
						        error: function(errorThrown){
						            console.log(errorThrown);
						        }
						    });  
				      }
				    }
				);
				*/
		});
	};

})( jQuery );
