<?php require_once 'templates/header.php' ?>
<svg class="chart"></svg>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">

<form id="reservation">
  <label id="dateLabel" for="dateSelect">Select Date</label>
  <select name="dates" id="dateSelect">
    <option>Date 1</option>
    <option>Date 2</option>
    <option>Date 3</option>
    <option>Date 4</option>
    <option>Date 5</option>
    <option>Date 6</option>
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

    $(function() {
        var select = $( "#dateSelect" );
        var slider = $( "<div id='slider'></div>" ).insertAfter( select ).slider({
          min: 1,
          max: 6,
          range: "min",
          value: select[ 0 ].selectedIndex + 1,
          slide: function( event, ui ) {
            select[ 0 ].selectedIndex = ui.value - 1;
          }
        });
        $( "#dateSelect" ).change(function() {
          slider.slider( "value", this.selectedIndex + 1 );
        });
      });

    var root;

    // ************** Generate the tree diagram	 *****************
    var margin = {top: 20, right: 120, bottom: 20, left: 120},
            width = 960 - margin.right - margin.left,
            height = 500 - margin.top - margin.bottom;

    var i = 0;

    var tree = d3.layout.tree().size([height, width]);

    var diagonal = d3.svg.diagonal().projection(function(d) { return [d.y, d.x]; });

    var svg = d3.select(".chart")
            .attr("width", width + margin.right + margin.left)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");


    d3.json("data/search_results_sdcc.json", function(json) {
        var data = massage(json);
                console.log(data);
                root = data;
                update();
            });

        function massage(tweetData) {
            var newData = {
                "name": tweetData.name,
                "weight": 500,
                "type": "search",
                "children": []
            };

            newData.children = digestChildren(tweetData.tweets,'tweet');

            for(var i = 0; i < newData.children.length; i++ ) {
                var child = newData.children[i];
            }

            return newData;
        }

        function digestChildren( children, type, depth ) {
            if(typeof(depth)==='undefined') depth = 0;
            if( depth < 2 ) {
                var toRet = [];
                for(var i = 0; i < children.length; i++ ) {
                    var tweetObj = children[i],
                        tweet = tweetObj.tweet,
                        childTweets = tweetObj.children,
                        nextGen = digestChildren(tweetObj.children, "rt_follow" , depth++).concat(digestChildren(tweetObj.adoptedChildren, "rt_nofollow" , depth++)),
                        child = {
                            "name": tweet.username,
                            "weight": tweet.followerCount,
                            "type": type,
                            "tweet": tweet,
                            "children": nextGen
                        };
                    toRet.push(child);
                }
                return toRet;
            }
            else {
                return [];
            }
        }

    var type_styles = {
        search:{
            circle:{
                fill: "#513D0B",
                stroke: "#412D00",
                strokeWidth: 3
            },
            text:{
                fill:"#444"
            }
        },
        tweet: {
            circle:{
                fill: "#90C81C",
                stroke: "#80B80C",
                strokeWidth: 2
            },
            text:{
                fill:"#444"
            }
        },
        rt_follow:{
            circle:{
                fill: "#0A8DA9",
                stroke: "#007D99",
                strokeWidth: 1
            },
            text:{
                fill:"#444"
            }
        },
        rt_nofollow:{
            circle:{
                fill: "#7DD4D1",
                stroke: "#6DC4C1",
                strokeWidth: 1
            },
            text:{
                fill:"#444"
            }
        }
    }

    function update() {

        // Compute the new tree layout.
        var nodes = tree.nodes(root).reverse();
        var links = tree.links(nodes);

        // Normalize for fixed-depth.
        nodes.forEach(function(d) { d.y = d.depth * 180; });

        // Declare the nodes…
        var node = svg.selectAll("g.node")
                .data(nodes, function(d) { return d.id || (d.id = ++i); });

        // Enter the nodes.
        var nodeEnter = node.enter().append("g")
                .attr("class", "node")
                .attr("transform", function(d) {
                    return "translate(" + d.y + "," + d.x + ")"; });

        nodeEnter.append("circle")
                .attr("r", function(d) { return 50; })
                .style("fill", function(d) { return type_styles[d.type].circle.fill; })
                .style("stroke", function(d) { return type_styles[d.type].circle.stroke; })
                .style("stroke-width", function(d) { return type_styles[d.type].circle.strokeWidth; })
                .on('click',click);

        nodeEnter.append('text')
                .attr("dy", ".35em")
                .attr("fill",function(d){ return type_styles[d.type].text.fill})
                .text(function(d){ return d.name;})
                .on('click',click);


        // Declare the links…
        var link = svg.selectAll("path.link")
                .data(links, function(d) { return d.target.id; });

        // Enter the links.
        link.enter().insert("path", "g")
                .attr("class", "link")
                .style("stroke", function(d) { return '#666'; })
                .style("fill", "none")
                .attr("d", diagonal);

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
</script>

<?php require_once 'templates/footer.php' ?>