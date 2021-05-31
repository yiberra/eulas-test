<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Entity\JSONFile;
use App\Entity\Product;

class ImportProductsCommand extends Command
{
    protected static $defaultName = 'import:products';
    protected static $defaultDescription = 'Import products from JSON file';

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
        
        $filePath = $this->params->get('json_file_path');

        // Get content of file
        $json = \file_get_contents($filePath);
        $json = \utf8_encode($json);
        
        // Check file content
        if(!$json){
            $output->writeln('<fg=white;bg=red>[ERROR] JSON file is empty or its corrupted</>');
            return Command::FAILURE;
        }

        // Check if json_decode fail
        if(json_last_error() !== JSON_ERROR_NONE){
            $output->writeln('<fg=white;bg=red>[ERROR] JSON file decode failed</>');
            return Command::FAILURE;
        }

        $products = \json_decode($json);

        if(!empty($products)){

            // New register on DB for JSON file
            $file = new JSONFile();
            $file->setName($filePath);
            $file->setState('waiting');
            $file->setCreatedAt(new \Datetime());
            $this->em->persist($file);
            $this->em->flush();

            $productsChunk = array_chunk($products, 1000);

            foreach($productsChunk as $kL => $list){

                foreach($list as $key => $product){

                    // Currency must be USD
                    if($product->price->currency !== 'USD'){
                        $output->writeln('<fg=white;bg=red>[ERROR] The product '.$product->styleNumber.' cannot be saved: the currency must be USD</>');
                        continue;
                    }

                    // Search product by styleNumber
                    $p = $this->em->getRepository(Product::class)->findOneBy(['styleNumber' => $product->styleNumber]);

                    // If product not exists, insert into DB
                    if(!$p){

                        $this->em->getConnection()->beginTransaction();

                        try {

                            $p = new Product();
                            $p->setStyleNumber($product->styleNumber);
                            $p->setName($product->name);
                            $p->setPriceAmount($product->price->amount);
                            $p->setPriceCurrency($product->price->currency);
                            $p->setImages($product->images);
                            $p->setState('toSync');
                            
                            $this->em->persist($p);
                            $this->em->flush();
                            $this->em->getConnection()->commit();

                        } catch (\Exception $e) {

                            $this->em->getConnection()->rollBack();
                            $output->writeln('<fg=white;bg=red>[ERROR] The product '.$product->styleNumber.' cannot be saved: '.$e->getMessage().'</>');

                        }
                    
                    // ... updated product
                    } else {

                        $this->em->getConnection()->beginTransaction();

                        try {

                            $p->setState('imported');

                            // If the product has changed change state to sync

                            if($p->getStyleNumber() != $product->styleNumber){
                                $p->setStyleNumber($product->styleNumber);
                                $p->setState('toSync');
                            }

                            if($p->getName() != $product->name){
                                $p->setName($product->name);
                                $p->setState('toSync');
                            }

                            if($p->getPriceAmount() != $product->price->amount){
                                $p->setPriceAmount($product->price->amount);
                                $p->setState('toSync');
                            }

                            if($p->getPriceCurrency() != $product->price->currency){
                                $p->setPriceCurrency($product->price->currency);
                                $p->setState('toSync');
                            }

                            if($p->getImages() != $product->images){
                                $p->setImages($product->images);
                                $p->setState('toSync');
                            }

                            $this->em->persist($p);
                            $this->em->flush();
                            $this->em->getConnection()->commit();

                        } catch (\Exception $e) {

                            $this->em->getConnection()->rollBack();
                            $output->writeln('<fg=white;bg=red>[ERROR] The product '.$product->styleNumber.' cannot be updated: '.$e->getMessage().'</>');
                            
                        }

                    }

                }

                unset($productsChunk[$kL]);

            }

            $file->setState('imported');
            $this->em->persist($file);
            $this->em->flush();

            $output->writeln('<fg=black;bg=green>[OK] JSON import successfully</>');
            
        } else {

            $output->writeln('<fg=black;bg=cyan>[OK] Products list is empty. Nothing to do.</>');

        }


        return Command::SUCCESS;

    }
}
