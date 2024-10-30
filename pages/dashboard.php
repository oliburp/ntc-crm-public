<?php
$pageTitle = "Dashboard";
include "panel.php";
?>
<style>
    main {
        background-image: url("../images/dashboard.png");
        background-repeat: no-repeat;
        background-size: cover;
    }
</style>
<main>
    <div class="container my-5">
        <div class="row justify-content-around align-items-stretch mainDashboard">
            <!-- Mission Section -->
            <div class="col-lg-5">
                <h4 class="fw-bold text-center">Our Mission</h4>
                <h6 class="fw-bold">Nova Schola inspires, prepares, and empowers students to succeed in a changing world.</h6>
                <p>This means:</p>
                <ul class="list-unstyled">
                    <li>We inspire students to learn and to develop as whole people: intellectually, physically, and emotionally.</li>
                    <li class="mt-2">We inspire students to continue learning throughout life.</li>
                    <li class="mt-2">We prepare and empower students to be successful by helping them develop the knowledge, skills, and abilities needed to enter or progress within the workforce and to adapt and thrive in our increasingly diverse and ever-changing world.</li>
                </ul>
            </div>
            <br id="dbBr" style="display: none;">

            <!-- Vision Section -->
            <div class="col-lg-5">
                <h4 class="fw-bold text-center">Our Vision</h4>
                <h6 class="fw-bold">To be a regional leader in transforming lives through a creative, rigorous, and compassionate approach to education.</h6>
                <p>This means:</p>
                <ul class="list-unstyled">
                    <li>We continually strive to strengthen and improve the positive impact we have on our students and community.</li>
                    <li class="mt-2">We will become known in the CALABARZON as an institution that “makes a difference.”</li>
                    <li class="mt-2">We continually strive to innovate – finding new and more effective ways to educate and serve students.</li>
                    <li class="mt-2">We sustain rigor in our work – holding high professional standards, international best practices, and high expectations for both our students and ourselves.</li>
                    <li class="mt-2">We approach our work with compassion – acknowledging the whole person, working with integrity and caring, accepting people where they are and moving them forward without sacrificing standards, values, or expectations; bringing joy, honesty, and understanding to our work.</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php
include "closing.php";
?>