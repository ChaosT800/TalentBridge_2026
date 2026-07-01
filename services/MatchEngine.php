<?php

class MatchEngine
{

    public static function calculate($conn,$student_id,$job_id)
    {

        $score=0;

        $breakdown=[];

        /*=========================================
        FETCH STUDENT DETAILS
        =========================================*/

        $sql="SELECT

        branch_id,

        cgpa,

        backlogs,

        graduation_year

        FROM students

        WHERE student_id=?";

        $stmt=mysqli_prepare($conn,$sql);

        mysqli_stmt_bind_param(

        $stmt,

        "i",

        $student_id

        );

        mysqli_stmt_execute($stmt);

        $student=mysqli_fetch_assoc(

        mysqli_stmt_get_result($stmt)

        );

        mysqli_stmt_close($stmt);

        if(!$student)
        {

            return [

                "score"=>0,

                "cgpa"=>0,

                "branch"=>0,

                "skills"=>0,

                "backlogs"=>0,

                "graduation"=>0

            ];

        }

        /*=========================================
        FETCH JOB DETAILS
        =========================================*/

        $sql="SELECT

        minimum_cgpa,

        maximum_backlogs,

        graduation_year

        FROM jobs

        WHERE job_id=?";

        $stmt=mysqli_prepare($conn,$sql);

        mysqli_stmt_bind_param(

        $stmt,

        "i",

        $job_id

        );

        mysqli_stmt_execute($stmt);

        $job=mysqli_fetch_assoc(

        mysqli_stmt_get_result($stmt)

        );

        mysqli_stmt_close($stmt);

        if(!$job)
        {

            return [

                "score"=>0,

                "cgpa"=>0,

                "branch"=>0,

                "skills"=>0,

                "backlogs"=>0,

                "graduation"=>0

            ];

        }

        /*=========================================
        CGPA (25)
        =========================================*/

        if($student['cgpa'] >= $job['minimum_cgpa'])
        {

            $score+=25;

            $breakdown['cgpa']=25;

        }
        else
        {

            $breakdown['cgpa']=0;

        }

        /*=========================================
        BACKLOGS (10)
        =========================================*/

        if($student['backlogs'] <= $job['maximum_backlogs'])
        {

            $score+=10;

            $breakdown['backlogs']=10;

        }
        else
        {

            $breakdown['backlogs']=0;

        }

        /*=========================================
        GRADUATION YEAR (10)
        =========================================*/

        if($student['graduation_year']==$job['graduation_year'])
        {

            $score+=10;

            $breakdown['graduation']=10;

        }
        else
        {

            $breakdown['graduation']=0;

        }

                /*=========================================
        BRANCH MATCHING (15)
        =========================================*/

        $sql="SELECT 1

        FROM job_branches

        WHERE job_id=?

        AND branch_id=?";

        $stmt=mysqli_prepare($conn,$sql);

        mysqli_stmt_bind_param(

        $stmt,

        "ii",

        $job_id,

        $student['branch_id']

        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_store_result($stmt);

        if(mysqli_stmt_num_rows($stmt)>0)
        {

            $score+=15;

            $breakdown['branch']=15;

        }
        else
        {

            $breakdown['branch']=0;

        }

        mysqli_stmt_close($stmt);

        /*=========================================
        FETCH STUDENT SKILLS
        =========================================*/

        $studentSkills=[];

        $sql="SELECT

        skill_id

        FROM student_skills

        WHERE student_id=?";

        $stmt=mysqli_prepare($conn,$sql);

        mysqli_stmt_bind_param(

        $stmt,

        "i",

        $student_id

        );

        mysqli_stmt_execute($stmt);

        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {

            $studentSkills[]=$row['skill_id'];

        }

        mysqli_stmt_close($stmt);

        /*=========================================
        FETCH JOB SKILLS
        =========================================*/

        $jobSkills=[];

        $sql="SELECT

        skill_id

        FROM job_skills

        WHERE job_id=?";

        $stmt=mysqli_prepare($conn,$sql);

        mysqli_stmt_bind_param(

        $stmt,

        "i",

        $job_id

        );

        mysqli_stmt_execute($stmt);

        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {

            $jobSkills[]=$row['skill_id'];

        }

        mysqli_stmt_close($stmt);

        /*=========================================
        SKILL MATCH SCORE (40)
        =========================================*/

        $matchedSkills=array_intersect(

        $studentSkills,

        $jobSkills

        );

        if(count($jobSkills)>0)
        {

            $skillScore=(

            count($matchedSkills)

            /

            count($jobSkills)

            )*40;

        }
        else
        {

            $skillScore=40;

        }

        $score+=$skillScore;

        $breakdown['skills']=round($skillScore,2);

                /*=========================================
        FINAL SCORE
        =========================================*/

        if($score>100)
        {

            $score=100;

        }

        if($score<0)
        {

            $score=0;

        }

        /*=========================================
        RETURN RESULT
        =========================================*/

        return [

            "score"=>round($score,2),

            "cgpa"=>$breakdown['cgpa'] ?? 0,

            "backlogs"=>$breakdown['backlogs'] ?? 0,

            "graduation"=>$breakdown['graduation'] ?? 0,

            "branch"=>$breakdown['branch'] ?? 0,

            "skills"=>$breakdown['skills'] ?? 0

        ];

    }

}

?>