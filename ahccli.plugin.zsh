# ------------------------------------------------------------------------------
#          FILE:  phpcli.plugin.zsh
#   DESCRIPTION:  oh-my-zsh phpcli plugin file.
#        AUTHOR:  Jitendra Adhikari (jiten.adhikary@gmail.com)
#       VERSION:  0.0.1
#       LICENSE:  MIT
# ------------------------------------------------------------------------------
# Specifically tuned to support autocompletion for apps build with adhocore/cli!
#         Check https://github.com/adhocore/php-cli#autocompletion
# ------------------------------------------------------------------------------

# PHPCli command completion
_phpcli_command_list () {
  command $1 --help 2>/dev/null | sed "1,/Commands/d" | grep -v Run | awk '/  [a-z]+ / { print $2 }'
}

# PHPCli option completion
_phpcli_option_list () {
  command $1 $2 --help 2>/dev/null | sed '1,/Options/d' | gawk 'match($0, /  .*(--[a-z-]+)(\.\.\.)?.    /, o) { print o[1] }'
}

# PHPCli compdef handler
_phpcli () {
  local curcontext="$curcontext" state line cmd subcmd
  typeset -A opt_args
  _arguments \
    '1: :->cmd'\
    '*: :->opts'

  cmd=`echo $curcontext | gawk 'match($0, /\:([_a-z-]+)\:$/, c) { print c[1] }'`
  subcmd=`echo $line | awk '{print $1}'`

  case $state in
    cmd) compadd $(_phpcli_command_list $cmd) ;;
    opts) compadd $(_phpcli_option_list $cmd $subcmd) ;;
  esac
}

#
# Register commands for autocompletion below:
#
# format:  compdef _phpcli <cmd>
# example: compdef _phpcli phint
#
