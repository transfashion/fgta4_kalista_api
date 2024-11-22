<?php

$endpoint = "https://kalista.localhost/api/Transfashion/KalistaApi/Customer/GetDataCustomerWhatsapp";


// Data yang akan dikirim
$data = [
	"request" => [
		"payload" => [
			"phone_number" => "6285885525565"
		]
	]
];

// Mengonversi data menjadi JSON
$jsonData = json_encode($data);

// Inisialisasi cURL
$ch = curl_init($endpoint);

// Mengatur opsi cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Menerima output sebagai string
curl_setopt($ch, CURLOPT_HEADER, true);         // Sertakan header dalam output
curl_setopt($ch, CURLOPT_NOBODY, false);        // Tetap sertakan body (ubah ke true jika hanya butuh header)
curl_setopt($ch, CURLOPT_POST, true); // Menggunakan metode POST
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json", // Header untuk JSON
    "Content-Length: " . strlen($jsonData)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Data yang dikirim

// Eksekusi cURL dan ambil responsnya
$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); // Ukuran header
$header = substr($response, 0, $header_size);           // Pisahkan header
$body = substr($response, $header_size);               // Pisahkan body (jika diperlukan)



// Cek apakah ada kesalahan
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
} else {
	echo $header;
    echo $body;
	echo "\n\n";
}

// Tutup cURL
curl_close($ch);