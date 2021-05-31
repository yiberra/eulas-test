<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Entity\JSONFile;
use App\Entity\Product;

class ExportCsvProductsCommand extends Command
{
    protected static $defaultName = 'export:csv:products';
    protected static $defaultDescription = 'Export changed products on CSV file';

    private $em;
    private $params;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->em = $em;
        $this->params = $params;        
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $filePath = $this->params->get('csv_file_path');
        if(!\file_exists($filePath)){
            $handle = \fopen($filePath, 'a+');
            \fclose($handle);
        }
        
        // Search product by toSync
        $products = $this->em->getRepository(Product::class)->findBy(['state' => 'toSync']);

        $data = [];

        if(!empty($products)){

            foreach($products as $k => $p){
                
                $data[] = array(
                    "Product Id" => $p->getStyleNumber(),
                    "Product Name" => $p->getName(),
                    "Price" => $p->getPriceCurrency().$p->getPriceAmount(),
                    "Image 1" => (isset($p->getImages()[0]) && $p->getImages()[0] != "" ? $p->getImages()[0] : ""),
                    "Image 2" => (isset($p->getImages()[1]) && $p->getImages()[1] != "" ? $p->getImages()[1] : ""),
                    "Image 3" => (isset($p->getImages()[2]) && $p->getImages()[2] != "" ? $p->getImages()[2] : ""),
                    "Image 4" => (isset($p->getImages()[3]) && $p->getImages()[3] != "" ? $p->getImages()[3] : ""),
                    "Image 5" => (isset($p->getImages()[4]) && $p->getImages()[4] != "" ? $p->getImages()[4] : ""),
                    "Image 6" => (isset($p->getImages()[5]) && $p->getImages()[5] != "" ? $p->getImages()[5] : ""),
                    "Image 7" => (isset($p->getImages()[6]) && $p->getImages()[6] != "" ? $p->getImages()[6] : ""),
                    "Image 8" => (isset($p->getImages()[7]) && $p->getImages()[7] != "" ? $p->getImages()[7] : ""),
                    "Image 9" => (isset($p->getImages()[8]) && $p->getImages()[8] != "" ? $p->getImages()[8] : "")
                );

            }

        }


        if(!empty($data)){

            // Serializer
            $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
            $context = array('csv_headers' => [
                "Product Id",
                "Product Name",
                "Price",
                "Image 1",
                "Image 2",
                "Image 3",
                "Image 4",
                "Image 5",
                "Image 6",
                "Image 7",
                "Image 8",
                "Image 9"
            ]);

            $csv = $serializer->encode($data, 'csv', $context);

            unset($data);

            if(file_put_contents($filePath, $csv)){

                $this->em->getConnection()->beginTransaction();

                try {

                    foreach($products as $k => $p){
                        $p->setState('synced');
                        $this->em->persist($p);
                    }

                    $this->em->flush();
                    $this->em->getConnection()->commit();

                    $output->writeln('<fg=black;bg=green>[OK] CSV file was created successfully</>');

                    return Command::SUCCESS;

                } catch (\Exception $e) {

                    $this->em->getConnection()->rollBack();

                    $output->writeln('<fg=black;bg=yellow>[WARNING] CSV file was created but DB transaction failed: '.$e->getMessage().'</>');

                }

            } else {

                $output->writeln('<fg=white;bg=red>[INFO] CSV file failed</>');
                return Command::FAILURE;

            }

        } else {

            $output->writeln('<fg=black;bg=green>[OK] Nothing to sync</>');
            return Command::SUCCESS;

        }
        
    }

}