<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cara Mengikuti Lelang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            background-color: #f0f0f0;
            /* Light gray background similar to the image */
            margin: 0;
            padding: 0;
            /* Changed to 0 to prevent footer overflow */
            box-sizing: border-box;
            flex-direction: column;
            align-items: center;
        }

        .container {
            background-color: #ffffff;
            /* White background for the main content area */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 40px;
            /* Add margin to separate from footer */
            margin-top: 20px;
            /* Add margin to separate from top of body */
        }

        h2 {
            color: #333;
            margin-bottom: 40px;
            font-size: 1.8em;
            text-align: center;
        }

        .flow-section {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            width: 100%;
        }

        .flow-step {
            background-color: #e0e0e0;
            /* Gray background for steps */
            padding: 15px 25px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            color: #555;
            white-space: nowrap;
            /* Prevent text wrapping */
        }

        .arrow {
            font-size: 2em;
            color: #888;
            margin: 0 15px;
        }

        .auction-process {
            border: 2px solid #ccc;
            /* Border around the "Ikuti Lelang" section */
            padding: 25px;
            margin-top: 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
            width: 70%;
            min-width: 300px;
            max-width: 500px;
            text-align: left;
            position: relative;
        }

        .auction-process h3 {
            margin-top: 0;
            color: #333;
            font-size: 1.4em;
            text-align: center;
            margin-bottom: 20px;
        }

        .auction-process ol {
            list-style-type: decimal;
            padding-left: 25px;
            margin: 0;
            color: #555;
        }

        .auction-process ol li {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .bottom-flow {
            display: flex;
            justify-content: space-around;
            width: 100%;
            margin-top: 40px;
            position: relative;
        }

        .bottom-flow .flow-step {
            width: 250px;
            /* Adjust width for better spacing */
            margin: 0 10px;
        }

        .contact-person {
            background-color: #b0b0b0;
            /* Gray background for contact person */
            padding: 20px 40px;
            border-radius: 8px;
            margin-top: 60px;
            font-weight: bold;
            color: #fff;
            text-align: center;
            width: 300px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .back-button {
            display: inline-block;
            background-color: #555;
            /* Darker gray for the button */
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            /* Remove underline */
            font-weight: bold;
            margin-top: 40px;
            /* Space from the contact person box */
            transition: background-color 0.3s ease;
            /* Smooth hover effect */
        }

        .back-button:hover {
            background-color: #777;
            /* Lighter gray on hover */
        }

        /* Footer styles */
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px;
            width: 100vw;
            /* Take 100% of the viewport width */
            margin-top: auto;
            /* Push footer to the bottom */
            /* Remove max-width and border-radius to make it truly full-screen */
            margin-left: calc(-50vw + 50%);
            /* Center the footer and extend full width */
            margin-right: calc(-50vw + 50%);
            /* Center the footer and extend full width */
            box-sizing: border-box;
            /* Include padding in the width calculation */
        }

        footer p {
            margin: 5px 0;
        }

        footer a {
            color: #aaffaa;
            /* Light green for links */
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }


        /* Responsive adjustments */
        @media (max-width: 768px) {
            .flow-section {
                flex-direction: column;
            }

            .arrow {
                margin: 10px 0;
                transform: rotate(90deg);
            }

            .auction-process {
                width: 90%;
            }

            .bottom-flow {
                flex-direction: column;
                align-items: center;
                margin-top: 20px;
                /* Reduce margin for stacked layout */
            }

            .bottom-flow .flow-step {
                margin-bottom: 20px;
                width: 80%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Cara Mengikuti Lelang</h2>

        <div class="flow-section">
            <div class="flow-step">Cari Sapi yang dilelang</div>
            <div class="arrow">→</div>
            <div class="flow-step">Cek Jadwal lelang</div>
            <div class="arrow">→</div>
            <div class="flow-step">Daftar sebagai penawar lelang</div>
        </div>

        <div class="auction-process">
            <h3>Ikuti lelang</h3>
            <ol>
                <li>Masukkan harga tertinggi terakhir</li>
                <li>Tunggu penawaran selanjutnya</li>
                <li>Waktu lelang habis (sesuai jadwal)</li>
                <li>Harga tertinggi terakhir jadi pemenang</li>
            </ol>
        </div>

        <div class="bottom-flow">
            <div class="flow-step">Ambil Sapi sesuai kesepakatan</div>
            <div class="flow-step">Pelunasan lelang</div>
        </div>

        <a href="../index.php" class="back-button">Kembali</a>

    </div>


    <?php include '../footer.php'; ?>

</body>

</html>