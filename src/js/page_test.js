const path = "../api/";

async function test_request() {
    console.log("click");

    await $.ajax({
        type: "POST",
        url: "/scientific_poster_generator/api/post_traffic.php",
        data: {
            action: "test-request"
        },
        success: function (response) {
            console.log(response)
        },
        error: function (err) {
            console.log(err);
        }
    });
}
