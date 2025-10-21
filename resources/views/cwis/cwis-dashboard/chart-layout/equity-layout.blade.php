
<style>
    /* Breakpoints */
    @media screen and (max-width: 540px) {
        .chart__figure {
            flex-direction: column;
            height: auto;
        }

        .chart__caption {
            margin: 15px auto auto;
            text-align: center;
            min-width: 160px;
        }

        .chart {
            width: 100%;
            margin-right: 0;
        }
    }

    /* Fonts (Google fonts) */
    .font--barlow {
        font-family: "Barlow Condensed", sans-serif;
    }

    .font--montserrat {
        font-family: "Montserrat", sans-serif;
    }

    .color--grey {
        color: #334466;
    }

    .color--green {
        color: #01713c;
    }

    /* Values */
    .canvas-size {
        width: 160px;
        height: 50px;
    }

    .font-weight--900 {
        font-weight: 900;
    }

    .animation-time--1400ms {
        animation-duration: 1400ms;
    }

    /* Fading animation */
    @keyframes fadein {
        0% {
            opacity: 0;
        }

        40% {
            opacity: 0;
        }

        80% {
            opacity: 1;
        }

        100% {
            opacity: 1;
        }
    }

    .main {
        display: grid;
    }

    .chart {
    position: relative;
    font-weight: 500;
    margin-right: 80px; /* Adjust the margin as needed */
    width: 50%;

    @media screen and (max-width: 540px) {
        width: 100%;
        margin-right: 0;
    }

    .chart__figure {
        display: flex;
        justify-content: center; /* Center horizontally */
        align-items: center; /* Center vertically */
        flex-direction: column; /* Stack items vertically */
        margin-bottom: 20px;
        height: 140px; /* Adjust the height as needed */

        @media screen and (max-width: 540px) {
            height: auto;
            margin-bottom: 0;
        }
    }

    .chart__canvas {
        margin: auto;
        width: 160px;
        height: 140px; /* Adjust the height as needed */
    }

    .chart__caption {
        display: flex;
        justify-content: center;
        flex-direction: column;
        margin-left: 30px;
        font-size: 36px;
        line-height: 56px;
        height: 100%;
        width: calc(80px + 160px);
        color: #01713c;

        @media screen and (max-width: 540px) {
            margin: 15px auto auto;
            text-align: center;
            min-width: 160px;
        }
    }
}


    /* Styles for each card container */
    .card {
        border: 1px solid rgb(231, 227, 227);
        width: 525px;
        height: 350px;
        margin: 10px 10px;
        display: grid;
        padding: 2px;

        background-color: #F4F9F7;
    }

    .card4 {
        width: 825px;
        place-items: none;
    }

    .card img {
        width: 200px;
        height: 200px;
    }

    .card-header {
        width: 100%;
        text-align: left;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #ccc;
        height: 50px;
    }

    /* heading */
    .card h2 {
        font-size: 12px;
    }

    /* paragraph */
    .card p {
        font-size: 15px;
    }

    span {
        position: absolute;
        top: 50%;
        left: 50%;
        text-align: center;
        font-size: 30px;
        margin-left: -25px;
        margin-top: -20px;
    }

    .chart-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
</style>


<div class="hi">
    <div class="card card1">
        <div style="text-align: center; height: auto;">
            <br>
            <br>
            <h2 style="color: #6CB4EE; font-weight: bold; font-size: 100px; margin-top:5%">
            {{ isset($eq1[0]) && $eq1[0] !== null ? html_entity_decode($eq1[0]->data_value) : 0  }}
            </h2>
            <br>
           
            <h2>{{__('Ratio of LIC access to total population access')}}</h2>
        </div>
    </div>
</div>




