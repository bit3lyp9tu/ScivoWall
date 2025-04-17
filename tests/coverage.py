import re
import sys


def getWithoutComments(list):
    l = []
    for line in list:
        i = line.find("//")
        if i != -1:
            l.append(line[:i])
        else:
            l.append(line)
    return l


def getFunctionList(file):
    f = open(file, "r")
    return {
        l
        for l in re.findall(
            r"(?<=function\s)\w+(?=\([\s,(\$\w+)]*\))",
            list_to_string(getWithoutComments(f.read().split("\n"))),
        )
    }


def getFunctionCalls(file):
    f = open(file, "r")
    return {
        l
        for l in re.findall(
            r"\w+(?=\()", list_to_string(getWithoutComments(f.read().split("\n")))
        )
    }


def getBuildInFuncPHP():
    f = open("./tests/php_build_in_func", "r")
    return {l.replace("\n", "") for l in f.readlines()}


def getFunctDifference(source_file, test_file):
    func = getFunctionList(source_file)
    calls = getFunctionCalls(test_file)

    buid_in_func = getBuildInFuncPHP()

    return (func - buid_in_func) - calls


def list_to_string(list):
    str = ""
    for i in list:
        str += i

    return str


def main():

    if len(sys.argv) == 2 + 1:
        # print(f"Difference of {sys.argv[1]} - {sys.argv[2]}")
        for l in getFunctDifference(sys.argv[1], sys.argv[2]):
            print(l)
    else:
        print()

    # f = open("./queries.php", "r")
    # for i in getWithoutComments(f.readlines()):
    #     print(i)


if __name__ == "__main__":
    main()
