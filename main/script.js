let data;

function readCSV() {
    fetch("./data.json") 
        .then((res) => {
        return res.json();
    })
    .then((csvData) => {
        data = csvData;
        init();
    });
}

function init() {
    const container = document.getElementById("cards-container");
    
    data = Array.isArray(data) ? data : [data];

    data.forEach((podcast) => {
        // Card
        const card = document.createElement("div");
        card.classList.add("card");

        // Title
        const titleDiv = document.createElement("div");
        titleDiv.classList.add("title-holder");
        const title = document.createElement("h3");
        title.textContent = podcast.title;
        titleDiv.appendChild(title);

        // Image
        const img = document.createElement("img");
        img.src = podcast.image_url;
        img.setAttribute("onclick", `openPopup(${podcast.podcast_id});`);

        // Artists button
        const artists = document.createElement("button");
        artists.classList.add("collapsible");
        artists.textContent = "Artistes";

        // Artists list
        const artistsList = document.createElement("div");
        artistsList.classList.add("collapsible-content");
        for (let a of podcast.artists.split(",")) {
            const li = document.createElement("ul");
            li.textContent = a;
            artistsList.appendChild(li);
        }

        // Audio button + handling
        const audioButton = document.createElement("button");
        audioButton.classList.add("media-player", "play");
        const audio = new Audio(podcast.audio_file);
        audioButton.addEventListener("click", function() {
            audioButton.classList.toggle("play");
            audioHandling(audio, podcast.podcast_id, audioButton);
        })

        // Audio progress
        const audioProgress = document.createElement("div");
        audioProgress.classList.add("progress");
        audioProgress.setAttribute("id", "progress"+podcast.podcast_id);

        // Append all to parent
        card.appendChild(titleDiv);
        card.appendChild(img);
        card.appendChild(audioButton);
        card.appendChild(audioProgress);
        card.appendChild(artists);
        card.appendChild(artistsList);

        container.appendChild(card);
    });

    collapse();
    shrinkTextToFit();
}

function collapse() {
    const coll = document.getElementsByClassName("collapsible");

    for (let i = 0; i < coll.length; ++i) {
        coll[i].addEventListener("click", function() {
            coll[i].classList.toggle("active");
            const content = this.nextElementSibling;
            if (content.style.maxHeight) content.style.maxHeight = null;
            else {
                content.style.overflow = "scroll";
                content.style.maxHeight = "5vw";
                // content.style.maxHeight = content.scrollHeight + "px";
            }
        });
    }
};

function audioHandling(audio, id, button) {
    // Play/pause handling
    if (!audio.paused) {
        audio.pause();
    } else if (audio.paused) {
        audio.play();
    }

    // Progress bar
    let timer;
    let percent = 0;

    audio.addEventListener("playing", function(_event) {
        const duration = _event.target.duration;
        advance(duration, audio);
    });

    audio.addEventListener("pause", function() {
        clearTimeout(timer);
    });

    const advance = (duration, element) => {
        if (audio.currentTime >= audio.duration-0.2) {
            button.classList.add("play");
        }
        
        const progress = document.getElementById("progress"+id);
        increment = 10/duration
        percent = Math.min(increment * element.currentTime * 10, 100);
        progress.style.width = percent+'%'
        startTimer(duration, element);
    }

    const startTimer = (duration, element) => { 
        if (percent < 100) {
            timer = setTimeout(function (){advance(duration, element)}, 100);
        }
    }
}

function filterCards() {
    const input = document.getElementById("search-input");
    const filter = input.value.toUpperCase();
    const cards = document.querySelectorAll(".card");

    cards.forEach(function(card) {
        const cardTitle = card.querySelector("h3").textContent;
        if (cardTitle.toUpperCase().indexOf(filter) > -1) {
            card.style.display = "flex"; // Display the card
        } else {
            card.style.display = "none"; // Hide the card
        }
    });
}

function openPopup(id) {
    const modal = document.querySelector('.modal');
    const modalContent = document.querySelector('.modal-content');
    modal.style.display = 'block';

    // Set the dimensions of the modal dynamically based on screen size
    const screenWidth = window.innerWidth;
    const screenHeight = window.innerHeight;

    const modalWidth = Math.min(screenWidth - 200, 800);
    const modalHeight = Math.min(screenHeight - 150, 700);

    modalContent.style.width = modalWidth + 'px';
    modalContent.style.height = modalHeight + 'px';

    // Setup iframe to send podcast_id to overview.php
    const iframe = document.querySelector('.iframe-container iframe');
    iframe.src = 'overview.php?data=' + encodeURIComponent(id);

    modal.addEventListener('click', closePopupOutside);
}

function closePopup() {
    const modal = document.querySelector('.modal');
    modal.style.display = 'none';
    modal.removeEventListener('click', closePopupOutside);
}

function closePopupOutside(event) {
    // Check if the click event occurred outside of the iframe
    const contentContainer = document.querySelector('.modal-content');
    if (contentContainer && !contentContainer.contains(event.target)) {
        closePopup();
    }
}

// Adjust card title font-size to fit on one line
function shrinkTextToFit() {
    const container = document.querySelectorAll('.card');
    const maxWidth = container[0].offsetWidth;

    container.forEach(c => {
        let fontSize = 1.2; // Initial font size
        const text = c.querySelector('h3');
        while (text.scrollWidth > maxWidth) {
            fontSize -= .1;
            text.style.fontSize = fontSize-.1 + 'vw';
        }
    })
}