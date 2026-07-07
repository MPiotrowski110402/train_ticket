/*
|--------------------------------------------------------------------------
| RailTicket Landing Page Scripts
|--------------------------------------------------------------------------
*/


document.addEventListener(
    "DOMContentLoaded",
    () => {


        /*
        |--------------------------------------------------------------------------
        | LOADER
        |--------------------------------------------------------------------------
        */


        const loader = document.getElementById(
            "loader"
        );


        if(loader){

            setTimeout(() => {

                loader.style.transition =
                    "opacity .7s ease";


                loader.style.opacity = "0";


                setTimeout(() => {

                    loader.remove();

                },700);


            },2200);

        }





        /*
        |--------------------------------------------------------------------------
        | NAVBAR SCROLL EFFECT
        |--------------------------------------------------------------------------
        */


        const navbar =
            document.querySelector(
                ".navbar"
            );


        const scrollProgress =
            document.querySelector(
                ".scroll-progress"
            );



        window.addEventListener(
            "scroll",
            () => {


                if(window.scrollY > 50){

                    navbar.classList.add(
                        "scrolled"
                    );

                }else{

                    navbar.classList.remove(
                        "scrolled"
                    );

                }



                /*
                Scroll progress
                */


                const height =
                    document.documentElement
                        .scrollHeight
                    -
                    document.documentElement
                        .clientHeight;



                const progress =
                    (
                        window.scrollY /
                        height
                    ) * 100;



                if(scrollProgress){

                    scrollProgress.style.width =
                        progress + "%";

                }


            }
        );





        /*
        |--------------------------------------------------------------------------
        | CURSOR GLOW
        |--------------------------------------------------------------------------
        */


        const cursor =
            document.createElement(
                "div"
            );


        cursor.className =
            "cursor-glow";


        document.body.appendChild(
            cursor
        );



        document.addEventListener(
            "mousemove",
            (e)=>{


                cursor.style.left =
                    e.clientX + "px";


                cursor.style.top =
                    e.clientY + "px";


            }
        );





        /*
        |--------------------------------------------------------------------------
        | PARALLAX BACKGROUND
        |--------------------------------------------------------------------------
        */


        const orbs =
            document.querySelectorAll(
                ".gradient-orb"
            );



        window.addEventListener(
            "mousemove",
            (e)=>{


                const x =
                    e.clientX /
                    window.innerWidth;



                const y =
                    e.clientY /
                    window.innerHeight;



                orbs.forEach(
                    (orb,index)=>{


                        const speed =
                            (index+1) * 15;



                        orb.style.transform =
                            `
                            translate(
                                ${x*speed}px,
                                ${y*speed}px
                            )
                            `;


                    }
                );


            }
        );





        /*
        |--------------------------------------------------------------------------
        | NUMBER COUNTERS
        |--------------------------------------------------------------------------
        */


        const counters =
            document.querySelectorAll(
                ".counter"
            );



        counters.forEach(
            counter => {


                const target =
                    Number(
                        counter.dataset.target
                    );


                let current = 0;


                const increment =
                    target / 100;



                const update =
                    () => {


                        current += increment;



                        if(current < target){


                            counter.innerText =
                                Math.ceil(
                                    current
                                );


                            requestAnimationFrame(
                                update
                            );


                        }else{


                            counter.innerText =
                                target;


                        }


                    };



                update();


            }
        );





        /*
        |--------------------------------------------------------------------------
        | REVEAL ON SCROLL
        |--------------------------------------------------------------------------
        */


        const revealElements =
            document.querySelectorAll(
                ".reveal"
            );



        const observer =
            new IntersectionObserver(
                entries => {


                    entries.forEach(
                        entry => {


                            if(
                                entry.isIntersecting
                            ){

                                entry.target.classList.add(
                                    "visible"
                                );


                            }


                        }
                    );


                },
                {
                    threshold:.15
                }
            );



        revealElements.forEach(
            el => {

                observer.observe(
                    el
                );

            }
        );





        /*
        |--------------------------------------------------------------------------
        | BUTTON RIPPLE EFFECT
        |--------------------------------------------------------------------------
        */


        const buttons =
            document.querySelectorAll(
                ".btn, .search-btn"
            );



        buttons.forEach(
            button => {


                button.addEventListener(
                    "click",
                    function(e){


                        const ripple =
                            document.createElement(
                                "span"
                            );


                        ripple.className =
                            "ripple";


                        const rect =
                            this.getBoundingClientRect();



                        ripple.style.left =
                            (
                                e.clientX -
                                rect.left
                            )
                            +
                            "px";


                        ripple.style.top =
                            (
                                e.clientY -
                                rect.top
                            )
                            +
                            "px";



                        this.appendChild(
                            ripple
                        );



                        setTimeout(
                            ()=>{
                                ripple.remove();
                            },
                            600
                        );


                    }
                );


            }
        );





        /*
        |--------------------------------------------------------------------------
        | TICKET INPUT FOCUS EFFECT
        |--------------------------------------------------------------------------
        */


        const inputs =
            document.querySelectorAll(
                ".ticket-card input, .ticket-card select"
            );



        inputs.forEach(
            input => {


                input.addEventListener(
                    "focus",
                    ()=>{

                        input.parentElement.classList.add(
                            "active"
                        );

                    }
                );



                input.addEventListener(
                    "blur",
                    ()=>{

                        input.parentElement.classList.remove(
                            "active"
                        );

                    }
                );


            }
        );





    }
);