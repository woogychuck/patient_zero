<?php require_once 'templates/header.php' ?>
<svg class="chart">
</svg>

<form id="reservation">
  <label id="dateLabel" for="dateSelect">Select Date</label>
  <select name="dates" id="dateSelect">
    <option value="1">Date 1</option>
    <option value="2">Date 2</option>
    <option value="3">Date 3</option>
    <option value="4">Date 4</option>
    <option value="5">Date 5</option>
    <option value="6">Date 6</option>
  </select>
</form>

<script id="tweetPopup" type="text/html">
    <div id="tweetPopup{{id}}" class="tweetPopup">
        <img class="avatar" src="{{userImageUrl}}" />
        <span class="displayName">{{displayName}}</span>
        <a href="https://www.twitter.com/#!/{{username}}" class="username">{{username}}</a>
        <span class="tweetCount">{{userTweetCount}} Tweets</span>
        <span class="followerCount">{{followerCount}} Followers</span>
        <div class="message">{{message}}</div>
        <span class="retweetCount"><i class="fa fa-retweet"></i> {{retweetCount}}</span>
        <span class="favoritedCount"><i class="fa fa-star"></i> {{favoritedCount}}</span>
        <span class="replyCount"><i class="fa fa-reply"></i> {{replyCount}}</span>
    </div>
</script>

<script>
    var root;

    // ************** Generate the tree diagram	 *****************
    var margin = {top: 20, right: 120, bottom: 20, left: 120},
            width = 960 - margin.right - margin.left,
            height = 500 - margin.top - margin.bottom;

    var i = 0;

    var tree = d3.layout.tree().size([height, width]);

    var diagonal = d3.svg.diagonal().projection(function(d) {
        if(d.type === 'rt_nofollow'){
            return [d.y + 50, d.x];
        }else{
            return [d.y, d.x];
        }
    });

    var svg = d3.select(".chart")
            .attr("width", width + margin.right + margin.left)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");


    function getData(offset){
        d3.json("data/search_results_sdcc_" + offset + ".json", function(json) {
            var data = massage(json);
                root = data;
                update();
        });
    }

    function massage(searchData) {
        var search = {
            "name": searchData.name,
            "weight": 500,
            "type": "search",
            "children": []
        };

        search.children = digestChildren(searchData.tweets,'tweet');

        return search;
    }

    function digestChildren( children, type ) {
        var tweets = [];
        for(var i = 0; i < children.length; i++ ) {
            var tweetObj = children[i],
                tweet = tweetObj.tweet,
                childTweets = tweetObj.children,
                child = {
                    "name": tweet.username,
                    "weight": tweet.followerCount,
                    "type": type,
                    "tweet": tweet
                };
                if(tweetObj.children || tweetObj.adoptedChildren){
                    var retweets = [];
                    if(tweetObj.children && tweetObj.children.length > 0)
                        retweets = digestChildren(tweetObj.children, "rt_follow");
                    if(tweetObj.adoptedChildren && tweetObj.adoptedChildren.length > 0)
                        retweets = retweets.concat(digestChildren(tweetObj.adoptedChildren, "rt_nofollow"));
                    child.children = retweets;
                };

            tweets.push(child);
        }
        return tweets;
    }

    var type_styles = {
        search:{
            circle:{r:5},
            text:{'dy':5,'dx':0, anchor:'middle'}
        },
        tweet: {
            circle:{r:3},
            text:{'dy':4,'dx':0, anchor:'middle'}
        },
        rt_follow:{
            circle:{r:2},
            text: {'dy':0,'dx':3, anchor:'start'}
        },
        rt_nofollow:{
            circle:{r:1.8},
            text: {'dy':0,'dx':3, anchor:'start'}
        }
    }

    function flatten(root) {
        var nodes = [], i = 0;

        function recurse(node) {
            if (node.children) node.children.forEach(recurse);
            if (!node.id) node.id = ++i;
            nodes.push(node);
        }

        recurse(root);
        return nodes;
    }

    function update() {

        // Compute the new tree layout.
        var nodes = tree.nodes(root).reverse();
        var links = tree.links(nodes);

        var circleScale = d3.scale.linear().domain([0, (nodes.length < 5 ? 5 : nodes.length)]);

        // Normalize for fixed-depth.
        nodes.forEach(function(d) { d.y = d.depth * 180; });

        // Declare the nodes…
        var node = svg.selectAll("g.node")
                .data(nodes, function(d) { return d.id || (d.id = ++i); });

        // Enter the nodes.
        var nodeEnter = node.enter().append("g")
                .attr("class", "node")
                .attr("transform", function(d) {
                    if(d.type === 'rt_nofollow'){
                        return "translate(" + (d.y + 50) + "," + d.x + ")";
                    }else{
                        return "translate(" + d.y + "," + d.x + ")"; }
                    }
                );

        nodeEnter.append("circle")
                .attr('r', function(d){ return circleScale(type_styles[d.type].circle.r) * 50; })
                .attr('class', function(d) { return 'node_'+d.type; })
                .on('click',click);

        nodeEnter.append('text')
                .attr('dy', function(d){ return (circleScale(type_styles[d.type].text.dy) * 75) + 5; })
                .attr('dx', function(d){ return circleScale(type_styles[d.type].text.dx) * 50; })
                .attr('class',function(d) { return 'text_'+d.type; })
                .attr('text-anchor',  function(d){ return type_styles[d.type].text.anchor; })
                .text(function(d){ return d.name;})
                .on('click',click);

        node.exit().remove();

        // Declare the links…
        var link = svg.selectAll("path.link")
                .data(links, function(d) { return d.target.id; });

        // Enter the links.
        link.enter().insert("path", "g")
                .attr("class", "link")
                .style("stroke", function(d) { return  d.target.type === 'rt_nofollow' ? '#888':'#666' })
                .style("stroke-dasharray",function(d) { return  d.target.type === 'rt_nofollow' ? '5,2':'0' })
                .style("fill", "none")
                .attr("d", diagonal);

        link.exit().remove();

        function click(d, i){
            var domElement = this,
                event = d3.event,
                relativeCoordinates = d3.mouse(domElement),
                selection = d3.select(domElement);

            var popup = $(PatientZero.Templating.compile('tweetPopup', d.tweet))
                .css({
                    position: 'absolute',
                    left: event.clientX,
                    top: event.clientY
                });

            var oldPopup = $('#' + popup.attr('id'));

            if (oldPopup.length > 0) {
                oldPopup.remove();
            }
            else {
                popup.appendTo(document.body);
            }
        }
    }

    $(function() {
            var select = $( "#dateSelect" );
            var slider = $( "<div id='slider'></div>" ).insertAfter( select ).slider({
              min: 1,
              max: 6,
              range: "min",
              value: select[ 0 ].selectedIndex + 1,
              slide: function( event, ui ) {
                select[ 0 ].selectedIndex = ui.value - 1;
                getData(ui.value);
              }
            });
            $( "#dateSelect" ).change(function() {
              slider.slider( "value", this.selectedIndex + 1 );
              getData(this.selectedIndex + 1);
            });

            getData(1);
          });

</script>

<?php require_once 'templates/footer.php' ?>