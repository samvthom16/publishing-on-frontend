// CALLBACK FUNCTION FOR THE WP API TO LOAD
wp.api.loadPromise.done( function() {
	
	// CALLBACK FUNCTION AFTER THE DOM HAS BEEN LOADED
	jQuery('document').ready(function(){
		
		console.log( pf_settings );
		
		/* BASE VIEW THAT EXTENDS WP VIEW */
		var BASE_VIEW = wp.Backbone.View.extend({
				
			prepare: function() {
				// BEFORE RENDERING, PASS THE JSON OBJECT OF THE MODEL TO THE TEMPLATE
				if ( ! _.isUndefined( this.model ) && _.isFunction( this.model.toJSON ) ) {
					return this.model.toJSON();
				} else {
					return {};
				}
			},
		});



		var pf = {};

		pf.form = BASE_VIEW.extend({
			hasSaved		: false,
			template		: wp.template('pf-form'),
			load_img  		: "#pf-loader",
			submission_form : '#pf-form',
			message_box 	: '#pf-message',
			events :{
				'click #pf-submit-post'			: 'formSubmit',
				'click #pf-draft-post'			: 'formSubmit',
				'click #pf-featured-image-link'	: 'selectFeaturedImage',
				'click #pf-continue-editing'	: 'continueEditing'
			},
			initialize: function(){
				
				var self = this;
			
				// ON CHANGE OF THE MODEL, RENDER THE ENTIRE FORM
				self.model.on("change", _.debounce(self.render, 300), self);
				
				self.render();
				
				// CUSTOMIZING THE TINYMCE EDITOR ON INIT
				self.customize_editor();	
			},
			
			/* ADD MORE BUTTONS TO THE TINY MCE, ESPECIALLY THE IMAGE BUTTON */
			customize_editor: function(){
			
				jQuery( document ).on( 'tinymce-editor-setup', function( event, editor ) {
					editor.settings.toolbar1 += ',alignleft,aligncenter,alignright,blockquote,image';
					
					// CALLBACK FUNCTION FOR THE IMAGE BUTTON - TRIGGER ON CLICK
					editor.addButton( 'image', {
						text: '',
						icon: 'image',
						onclick: function (event) {
							
							var elem = jQuery( event.currentTarget ),
								editor = elem.data('editor'),
								options = {
									frame:    'post',
									state:    'insert',
									title:    wp.media.view.l10n.addMedia,
									multiple: true
								};
							
							event.preventDefault();
							
							// OPEN MEDIA LIBRARY
							wp.media.editor.open( editor, options );
							
							
						}
					});
					
					
				});

				
				
			},
			
			
			render: function(){
				
				var self = this,
					data = self.model.toJSON();		// CONVERT MODEL OBJECT TO JSON OBJECT FOR INSERTING INTO TEMPLATE
				
				// IF THE MODEL IS BEING CHANGED AFTER THE SUBMIT OR DRAFT BUTTON IS PRESSED THEN STOP RENDERRING
				if( self.hasSaved ) { return this;}
			
				// RESET THE SAVED FLAG
				self.hasSaved = false;
					
				// IF THE POST TITLE IS EMPTY
				if( _.isUndefined( self.model.get('title') ) ){
					data['title'] = { rendered: '' };
				}
			
				// IF THE POST CONTENT IS EMPTY
				if( _.isUndefined( self.model.get('content') ) ){
					data['content'] = { rendered: '' };
				}
				
				// IF THE FEATURED MEDIA IS EMPTY
				if( _.isUndefined( self.model.get('featured_media') ) ){
					data['featured_media'] = 0;
				}
				
				// RENDER THE POST MODEL INTO THE TEMPLATE FORM
				self.$el.html( self.template( data ) );
				
				// REMOVE TINYMCE INCASE IT ALREADY HAS BEEN INSTANTIATED
				wp.editor.remove('pf-content');
				
				// INITIALIZE TINYMCE
				wp.editor.initialize('pf-content', { tinymce: true} );
				
				
				
				self.hideLoader();
				
			},
			setFeaturedImage: function( url ){
				
				// SHOW THE SELECTED FEATURED IMAGE ON THE FORM
				jQuery('#pf-featured-image-container').attr('style', 'background-image:url(" ' + url + '");');
				
			},
			selectFeaturedImage: function(ev){
				
				ev.preventDefault();
				var self = this;
				
				// INIT MEDIA LIBRARY OPTIONS
				var custom_uploader = wp.media.frames.file_frame = wp.media({
					title: 'Choose Featured Image',
					button: {
						text: 'Choose Image'
					},
					multiple: false
				});
				
				// ON SELECTION OF AN IMAGE IN THE MEDIA LIBRARY
				custom_uploader.on('select', function() {
					
					// JSON OBJECT OF THE IMAGE SELECTED
					attachment = custom_uploader.state().get('selection').first().toJSON();
					
					/* UPDATE THE MEDIA ID TO THE FORM FIELD */
					jQuery('input[name=post_image]').val( attachment.id );
					
					self.setFeaturedImage( attachment.url );
					
				});
				
				// OPEN MEDIA LIBRARY
				custom_uploader.open();
				
				
			},
			get_post_status: function( post_status ){
				
				if( post_status != 'draft' &&  "0" == pf_settings['disable_moderation'] ){
					// CHANGE POST STATUS - 
					post_status = 'pending';
					
				}
				
				return post_status;
				
			},
			formSubmit: function(ev){
			
				/* SAVE THE TINYMCE HTML TO THE CONTENT TEXTAREA */
				tinyMCE.triggerSave();
				
				/* INIT ALL VARIABLES : SELF OBJECT AND GET ALL FORM VALUES */
				var self 	= this,				
					title 	= this.$el.find('input[name=post_title]').val(),
					status 	= this.get_post_status( jQuery(ev.currentTarget).data('status') ),
					f_image	= this.$el.find('input[name=post_image]').val(),
					today 	= new Date().toISOString(),
					content = this.$el.find('textarea[name=post_content]').val();
				
				// VALIDATE THE FORM
				if( !self.validate(title, content) ){ return false; }
				
				// CHECK FOR SPAM KEYWORDS AND STOP PUBLISHING IF ANY
				if( status == 'publish' && !self.checkForSpam(title, content) ){ return false; }
				
				// SET FLAG
				self.hasSaved = true;
				
				
				// ADDING THE FORM VALUES TO THE MODEL
				self.model.set({ title: title, content: content, status: status, featured_media: f_image, date: today });
				
				// SHOW LOADER
				self.showLoader();
				
				
				// SAVE THE MODEL TO DB
				self.model.save(null, {
					'success': function(){
						
						// POST MESSAGE AFTER SAVING
						self.afterPostSave(status);
					},
					'error': function(m, r){
						
						// SHOW ERROR MESSAGE
						self.displayMessage( r.responseJSON.message );
						
					}
				});
			},
			hideLoader: function(){
				/* HIDING THE LOADER */
				this.$el.find(this.load_img).hide();
			},
			showLoader: function(){
				/* SHOWING THE LOADER */
				this.$el.find(this.load_img).show();
				
				/* DISABLE THE FORM BUTTONS WHILE THE LOADER IS ON */
				this.$el.find('button').attr('disabled', 'disabled');
				
			},
			hideForm: function(){
				
				// HIDING THE FORM
				this.$el.find(this.submission_form).hide();
				
				// HIDE THE LOADER ALSO
				this.hideLoader();
				
			},
			showForm: function(){
				
				// HIDE THE MESSAGE BOX IF DISPLAYED
				this.$el.find( this.message_box ).hide();
				
				// DISPLAYING THE FORM
				this.$el.find( this.submission_form ).show();
				
			},
			continueEditing: function(ev){
				ev.preventDefault();
				
				// RESET THE SAVED FLAG
				self.hasSaved = false;
				
				// SHOW THE FORM
				this.showForm();
			},
			displayMessage: function( msg, message_box_class ){
				
				msg = this.stringFromTemplate( msg, { continueEditing : "<a id='pf-continue-editing' href='#'>Continue Editing</a>", });
				
				message_box_class = typeof message_box_class !== 'undefined' ? message_box_class : 'warning';
				
				// HIDE THE SUBMISSION FORM
				this.hideForm();
						
				
				var message_box = this.$el.find( this.message_box );
				
				// ADDING THE APPROPRIATE CLASS TO THE MESSAGE BOX
				message_box.attr( 'class', message_box_class );
				
				// ADDING THE MSG TO THE DOM HTML
				message_box.html(msg);
				
				// DISPLAYING THE HIDDEN MESSAGE BOX
				message_box.show();
				
				// NAVIGATING TO THE TOP OF THE BODY
				jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
				
				
			},
			afterPostSave: function(status){
				
				var self 			= this, 											// OBJECT ITSELF
					title 			= self.model.get('title').rendered,					// POST TITLE
					permalink 		= self.model.get('link'),							// POST LINK
					msg 			= '';												// MESSAGE TO BE DISPLAYED
					
				switch(status){
					
					case 'draft':
						msg = pf_settings['message_draft'];
						break;
					
					default:
						msg = pf_settings['message'];
						
				}
				
				msg = this.stringFromTemplate( msg, { permalink : permalink, title : title});
				
				// SHOW SUCCESS MESSAGE
				self.displayMessage( msg, 'success' );
				
			},
			checkForSpam: function( title, content ){
				
				// IF THE SPAM WORDS HAVE NOT BEEN ADDED THEN RETURN
				if( typeof pf_settings == 'undefined' ) return true;
				
				var flagWords 	= pf_settings['spam_words'],
					spamMsg		= pf_settings['message_spam'],
					length 		= flagWords.length,
					self 		= this;
				
				// CHANGING TO THE LOWER CASE AND REMOVE ALL WHITE SPACES FROM THE SENTENCE
				title 	= title.toLowerCase().replace(/\s/g,'');
				content = content.toLowerCase().replace(/\s/g,'');
				
				// ITERATE THROUGH EACH SPAM WORD
				while( length-- ) {
					
					// CHECK IF THE WORD EXISTS IN THE TITLE OR POST CONTENT
					if ( title.indexOf(flagWords[length].toLowerCase()) != -1 || content.indexOf(flagWords[length].toLowerCase()) != -1 ) {
							
						self.displayMessage(spamMsg);
			  
						return false;
						
					}
						
				}
				return true;
			},
			validate: function( title, content ){
				var self 	= this;
					
				// CHECK IF THE TITLE AND CONTENT ARE EMPTY
				if( !title || !content ){
					
					// SHOW ERROR MESSAGE
					self.displayMessage( pf_settings['message_empty'] );
					
					return false;
				}
				
				return true;
			},
			stringFromTemplate: function(template, variables) {
				
				if( ! template ) return template;
				
				return template.replace(new RegExp("\{([^\{]+)\}", "g"), function(_unused, varName){
					if( variables[varName] ){
						return variables[varName];
					}
					return _unused;
				});
			}
		});
		
		
		
		jQuery('[data-behaviour~=pf-form]').each(function(){
			
			var el = jQuery(this);
			
			new pf.form( { el: el, model: new wp.api.models.Post() } );
		
		});
		
	});
	
});


