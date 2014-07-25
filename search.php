<?php require_once 'templates/header.php' ?>

<h1>Patient Zero Search</h1>

<form id="search">
    <input id="searchTerm" placeholder="Search" />
    <input type="submit" value="Search" />

    <div id="#results"></div>
</form>

<script>
$(document).ready(function () {
    $('#search').on('submit', function (event) {
        PatientZero.Search.getResults({term: $('#searchTerm').val()}).then(function (data) {
            var resultDisplay = $('div');

            $.each(data.tweets, function (index, tweet) {
                resultDisplay.append('<div>' + tweet.tweet.username + '</div>');
            });

            $('#results').replaceWith(resultDisplay);
        });

        event.preventDefault();
    });
});
</script>

<?php require_once 'templates/footer.php' ?>