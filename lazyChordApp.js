var ChordsModel = Backbone.Model.extend({
	defaults : {
	    intro : [],
	    verse : [],
	    chorus : [],
	    bridge : [],
	    capoRestrict : 12,
	    capoPosn : 0,
	    transposition : 0,
	    transposedIntro : [],
	    transposedVerse : [],
	    transposedChorus : [],
	    transposedBridge : [],
	    components : {
	    	"intro" : "verse",
	    	"verse" : "chorus",
	    	"chorus" : "bridge",
	    	"bridge" : ""
	    }
	}
});
var chordsModel = new ChordsModel();

// the home view with welcome message
var HomeView = Backbone.View.extend({
	el: '.contentContainer',
	render : function () {
		//alert("helo");
		var template = _.template($('#homeViewTemplate').html(), {});
		this.$el.html(template);
	}
});

var homeView = new HomeView();

// the home view with welcome message
var EnterChordsView = Backbone.View.extend({
	el: '.contentContainer',
	events : {
		'click .addChordBtn' : 'addChord'

	},
	elmts : {
		'ADD_CHORD_INPUT' : '.addChordInput'
	},
	addChord : function(ev) {
		var input = $(this.elmts['ADD_CHORD_INPUT']);
		if (input.val()) {
    		var component = input.attr("id").split("_")[1];
    		var arr = _.clone(chordsModel.get(component));
    		arr.push(input.val());
    		chordsModel.set(component, arr);
    	}
		return false;
	},

	render : function (options) {
		if (options.component) {
			var template = _.template($('#enterChordsViewTemplate').html(), { component : options.component });
			this.$el.html(template);
		}
		
	},
	// initialize function can be put here
	renderChords : function (component) {
		
		var template = _.template($('#chordsListTemplate').html(), { chords : chordsModel.get(component) });
		$('.chordsList').html(template);
	}
});

var enterChordsView = new EnterChordsView();

var SetOptionsView = Backbone.View.extend({
	el : '.contentContainer',
	events : {
		'change #capoRestrictSelect' : 'setRestrict'
	},
	setRestrict : function (ev) {
		chordsModel.set("capoRestrict", parseInt($('#capoRestrictSelect').val()));
		return false;
	},
	render : function () {
		var template = _.template($('#setOptionsViewTemplate').html(), {});
		this.$el.html(template);
	}
});
var setOptionsView = new SetOptionsView();

var ResultView = Backbone.View.extend({
	el : '.contentContainer',
	render : function () {
		var template = _.template($('#resultViewTemplate').html(), { });
		this.$el.html(template);
	},
	getTransposed : function () {
		var dataToSend = {
			'intro' : chordsModel.get('intro'),
			'verse' : chordsModel.get('verse'),
			'chorus' : chordsModel.get('chorus'),
			'bridge' : chordsModel.get('bridge'),
			'restrict' : chordsModel.get('capoRestrict')
		};
		var that = this;
		jQuery.ajax({
            type: 'POST',
            url: 'getTransposed.php',
            data: dataToSend,
            dataType: 'json',
            success: function (data) {
                console.log("success!");
                chordsModel.set("transposedIntro", data["intro"]);
                chordsModel.set("transposedVerse", data["verse"]);
                chordsModel.set("transposedChorus", data["chorus"]);
                chordsModel.set("transposedBridge", data["bridge"]);
                chordsModel.set("capoPosn", data["capoPosn"]);
                chordsModel.set("transposition", data["transposition"]);

                that.render();
            },
            error: function() {
            	console.log("error");
            }
        });
	}
});
var resultView = new ResultView();

chordsModel.on('change:intro', function () { return enterChordsView.renderChords("intro"); });
chordsModel.on('change:verse', function () { return enterChordsView.renderChords("verse"); });
chordsModel.on('change:chorus', function () { return enterChordsView.renderChords("chorus"); });
chordsModel.on('change:bridge', function () { return enterChordsView.renderChords("bridge"); });


var Router = Backbone.Router.extend({
    routes: {
      "": "home",
      "enterChords/:component" : "enterChords",
      "setOptions" : "setOptions",
      "result" : "result"
    }
});


var router = new Router();
router.on('route:home', function() {
  // render user list
  homeView.render();
});
router.on('route:enterChords', function (component) {
	enterChordsView.render({ component: component });
});
router.on('route:setOptions', function () {
	setOptionsView.render();
});
router.on('route:result', function () {
	resultView.getTransposed();
});
Backbone.history.start();