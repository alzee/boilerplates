<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'adduser',
    description: 'Create user',
)]
class AddUserCommand extends Command
{
    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $hasher)
    {
        $this->em = $em;
        $this->hasher = $hasher;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addOption('super', 's', InputOption::VALUE_NONE, 'Is super')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Is admin')
            ->addOption('root', null, InputOption::VALUE_NONE, 'Is root')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $roles = [];
        ;
        if ($input->getOption('admin')) {
            $roles = ["ROLE_ADMIN"];
            $gid = 200;
        }

        if ($input->getOption('super')) {
            $roles = ["ROLE_SUPER_ADMIN"];
            $gid = 100;
        }

        if ($input->getOption('root')) {
            $roles = ["ROLE_ROOT"];
            $gid = 0;
        }
        
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->hasher->hashPassword($user, $password));
        $user->setRoles($roles);
        $user->setGid($gid);
        $this->em->persist($user);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
