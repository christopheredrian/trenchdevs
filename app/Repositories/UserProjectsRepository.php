<?php

namespace App\Repositories;

use App\Models\Users\ProjectUser;
use App\Models\Users\UserDegree;
use App\Models\Users\UserProject;
use App\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class UserProjectsRepository
{
    /**
     * @param User $forUser
     * @param array $rawProjects
     * @return UserDegree[]
     * @throws
     */
    public function saveRawProjects(User $forUser, array $rawProjects): array
    {

        $userProjects = [];

        try {

            DB::beginTransaction();

            if (!isset($forUser->id)) {
                throw new InvalidArgumentException("Invalid user given");
            }

            if (empty($rawProjects)) {
                throw new InvalidArgumentException("Empty projects given");
            }

            foreach($forUser->projects as $oldProject) {
                $this->deleteProject($oldProject);
            }

            foreach ($rawProjects as $rawProject) {
                $userProject = new UserProject();
                $rawProject['user_id'] = $forUser->id;
                $userProject->fill($rawProject);
                $userProject->saveOrFail();
                $userProjects[] = $userProject;

                if(!$userProject->is_personal){
                    foreach ($userProjects['users'] as $user) {
                        $projectUser = new ProjectUser();
                        $projectUser->user_id = $user->id;
                        $projectUser->project_id = $userProject->id;
                        $projectUser->saveOrFail();
                    }
                }
            }

            DB::commit();

        } catch (Throwable $exception) {
            DB::rollBack();
            $userProjects = [];
            throw $exception;
        }

        return $userProjects;

    }

    public function deleteProject(UserProject $userProject){

        try {
            DB::beginTransaction();

            if(!$userProject->is_personal){
                foreach ($userProject->projectUsers as $projectUser) {
                    $projectUser->delete();
                }
            }

            $userProject->delete();

            DB::commit();

        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return true;
    }
}
