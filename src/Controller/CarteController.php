<?php

namespace App\Controller;

use App\Form\CarteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarteController extends AbstractController
{
    /**
     * @Route("", name="app_carte")
     */
    public function index(Request $request): Response
    {

        //Formulaire
        $form = $this->createForm(CarteType::class);
        $form->handleRequest($request);

        $depart ='';
        $arrive ='';
        if ($form->isSubmitted() && $form->isValid()) {
            $depart = $form->get('depart')->getData();
            $arrive = $form->get('arrive')->getData();
        }
        //Géocoder départ
        $addressDepart = $depart;

        $referer = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($addressDepart) . '&format=jsonv2&addressdetails=1&limit=1';
        $opts = array(
            'http' => array(
                'header' => array("Referer: $referer\r\n")
            )
        );
        $context = stream_context_create($opts);
        $myURL = file_get_contents($referer, false, $context);

        $resp = json_decode($myURL, true);


        $latDepart ='';
        $longDepart ='';
        if( $resp) {
            $latDepart = $resp[0]['lat'];
            $longDepart = $resp[0]['lon'];
        }

        //Géocoder arrivée

        $addressArrivee = $arrive;

        $referer = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($addressArrivee) . '&format=jsonv2&addressdetails=1&limit=1';
        $opts = array(
            'http' => array(
                'header' => array("Referer: $referer\r\n")
            )
        );
        $context = stream_context_create($opts);
        $myURL = file_get_contents($referer, false, $context);

        $resp = json_decode($myURL, true);

        $latArrivee ='';
        $longArrivee ='';
        if( $resp) {
            $latArrivee = $resp[0]['lat'];
            $longArrivee = $resp[0]['lon'];
        }



        $distance = '';

        //Distance entre 2 points
            $earth_radius = 6378137;   // Terre = sphère de 6378km de rayon
        if($resp) {
            $rlo1 = deg2rad($longDepart);
            $rla1 = deg2rad($latDepart);
            $rlo2 = deg2rad($longArrivee);
            $rla2 = deg2rad($latArrivee);

            $dlo = ($rlo2 - $rlo1) / 2;
            $dla = ($rla2 - $rla1) / 2;
            $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin(
                        $dlo));
            $d = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = ($earth_radius * $d)/1000;
        }

        return $this->render('carte/index.html.twig', [
            'formCarte' => $form->createView(),
            'latDepart' => $latDepart,
            'longDepart' => $longDepart,
            'longArrivee' => $longArrivee,
            'latArrivee' => $latArrivee,
            'depart' => $depart,
            'arrivee' => $arrive,
            'distance' => $distance
        ]);

    }
}
