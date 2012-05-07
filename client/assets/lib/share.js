

  
 function share(){
	 FB.ui({
			method: 'feed',
			name: 'I posted a unicorn or bacon on this location',
			caption: 'Bacon Unicorn Game',
			description: 'Check out more bacon and unicorns!',
			link: 'http://staciehibino.com/bug/mmm3',
			picture: 'http://www.banane.com/annafbtest/unicorn.jpg'
		}, 
		function(response) {
			console.log('publishStory response: ', response);
		});
		return false;
	 }
